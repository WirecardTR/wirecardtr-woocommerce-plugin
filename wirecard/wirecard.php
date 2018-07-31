<?php
error_reporting(E_ALL);

/*
  Plugin Name: Wirecard Türkiye Kredi Kartı İle Ödeme
  Plugin URI: http://developer.wirecard.com.tr
  Description: Wirecard Kredi Kartı ile ödeme
  Version: 1.1
  Author: Wirecard adına Codevist BT
  Author URI: http://www.codevist.com
 */

/* Define the database prefix */
global $wpdb;
define("_DB_PREFIX_", $wpdb->prefix);

/* Install Function */
register_activation_hook(__FILE__, 'myplugin_activate');

function myplugin_activate()
{
	global $wpdb;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'wirecard` (
	  `order_id` int(10) unsigned NOT NULL,
	  `customer_id` int(10) unsigned NOT NULL,
	  `wirecard_id` varchar(64) NULL,
	  `amount` decimal(10,4) NOT NULL,
	  `amount_paid` decimal(10,4) NOT NULL,
	  `installment` int(2) unsigned NOT NULL DEFAULT 1,
	  `cardholdername` varchar(60) NULL,
	  `cardnumber` varchar(25) NULL,
	  `cardexpdate` varchar(8) NULL,
	  `createddate` datetime NOT NULL,
	  `ipaddress` varchar(16) NULL,
	  `status_code` tinyint(1) DEFAULT 1,
	  `result_code` varchar(60) NULL,
	  `result_message` varchar(256) NULL,
	  `mode` varchar(16) NULL,
	  `shared_payment_url` varchar(256) NULL,
	  KEY `order_id` (`order_id`),
	  KEY `customer_id` (`customer_id`)
	) DEFAULT CHARSET=utf8;';
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
	return dbDelta($sql);
}

/* wirecard All Load */
add_action('plugins_loaded', 'init_wirecard_gateway_class', 0);

function init_wirecard_gateway_class()
{
	if (!class_exists('WC_Payment_Gateway'))
		return;

	class wirecard extends WC_Payment_Gateway
	{
	
		function __construct()
		{
			$this->id = "wirecard";
			$this->method_title = "Wirecard Kredi Kartı İle Ödeme";
			$this->method_description = "Kredi kartı ile peşin ve taksitli ödeme";
			$this->title = 'Kredi kartı ile Ödeme';
			$this->icon = null;
			$this->has_fields = true;
			$this->supports = array('default_credit_card_form');
			$this->init_form_fields();
			$this->init_settings();
			$this->version = 1.1;
			
			foreach ($this->settings as $setting_key => $value)
				$this->$setting_key = $value;
			//Register the style
			add_action('admin_enqueue_scripts', array($this, 'register_admin_styles'));
			add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
			add_action('woocommerce_thankyou_' . $this->id, array($this, 'receipt_page'));
			if (is_admin()) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			}

			// WirecardTokenSettings
			$wirecard_settings = get_option("woocommerce_wirecard_settings");
			$this->endpoint = "https://www.wirecard.com.tr/SGate/Gate";
			$this->wirecard_usercode = $wirecard_settings["wirecard_usercode"];
			$this->wirecard_pin = $wirecard_settings["wirecard_pin"];
			
			
			}

		public function register_admin_styles()
		{
			wp_register_style('admin', plugins_url('css/admin.css', __FILE__));
			wp_enqueue_style('admin');
		}


		public function admin_options()
		{
			
			echo '<h1>Wirecard Ödeme Ayarları</h1><hr/>';
			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

			include(dirname(__FILE__).'/includes/footer.php');
		}

		/* 	Admin Panel Fields */

		public function init_form_fields()
		{
			$this->form_fields = array(
				'enabled' => array(
					'title' => 'Eklenti Aktif',
					'label' => 'Eklenti Aktif Mi?',
					'type' => 'checkbox',
					'default' => 'yes',
				),
				'wirecard_usercode' => array(
					'title' => 'Wirecard User Code',
					'type' => 'text',
					'desc_tip' => 'Wirecard tarafından atanan üye iş yeri numarası',
				),
				'wirecard_pin' => array(
					'title' => 'Wirecard Pin',
					'type' => 'text',
					'desc_tip' => 'Wirecard tarafından atanan pin',
				),
					'installment' => array(
					'title' => 'Taksit',
					'label' => 'Taksitli İşlem Aktif Mi? <br><strong>(Sadece Üye İş Yeri Ödeme Sayfası yönteminde çalışmaktadır.)</strong>',
					'type' => 'checkbox',
					'desc_tip' => 'Taksitli ödemeye izin verecekmisiniz?',
					'default' => 'yes',
				),
				'mode' => array(
					'title' => 'Ödeme Yöntemi',
					'type' => 'select',
					'desc_tip' => 'Ödeme Yönteminiz?',
					'default' => 'form',
					'options' => array(
						'shared3d' => 'Ortak Ödeme Sayfası (3DS li) ',
						'shared' => 'Ortak Ödeme Sayfası (3DS siz)',
						'form' => 'Üye İş Yeri Ödeme Sayfası',
					),
				)
			);
		}

// End init_form_fields()

		public function process_payment($order_id)
		{
			global $woocommerce;
			$order = new WC_Order($order_id);
			if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
      
                $checkout_payment_url = $order->get_checkout_payment_url(true);
            } else {
         
                $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
            }
			

            return array(
                'result' => 'success',
                'redirect' => $checkout_payment_url,
            );
			
		}

//END process_payment



		public function credit_card_form($args = array(), $fields = array())
		{
			?>
			<p>Ödemenizi tüm kredi kartları ile yapabilirsiniz. </p>
			
		
			<?php
		}

		public function createSecret($key)
		{
			return sha1('wirecard' . $key);
		}


		function pay($order_id)
		{
			
			global $woocommerce;
			get_currentuserinfo();
			$order = new WC_Order($order_id);
			require_once( plugin_dir_path(__FILE__) . 'includes/restHttpCaller.php');
			require_once( plugin_dir_path(__FILE__) . 'includes/CCProxySaleRequest.php');
			require_once( plugin_dir_path(__FILE__) . 'includes/WDTicketPaymentFormRequest.php');
			require_once( plugin_dir_path(__FILE__) . 'includes/BaseModel.php');
			require_once( plugin_dir_path(__FILE__) . 'includes/helper.php');
		

			$user_meta = get_user_meta(get_current_user_id());
			$installment = (int) $_POST['wirecard-installment-count']; 
				
			$amount = $order->order_total;
			$user_id = get_current_user_id();
		

			$wirecard_settings = get_option("woocommerce_wirecard_settings"); 
			
			$usercode = $wirecard_settings["wirecard_usercode"];
			$pin =$wirecard_settings["wirecard_pin"];
			$mode = $wirecard_settings["mode"];
			
		
			$expire_date = explode('/', $_POST['wirecard-card-expiry']);

				$record = array(
					'order_id' => $order_id,
					'customer_id' => $user_id,
					'wirecard_id' => $order_id,
					'amount' => $amount,
					'amount_paid' => $amount,
					'installment' => $installment,
					'cardholdername' => $_POST['wirecard-card-name'],
					'cardexpdate' => str_replace(' ', '', $expire_date[0]) . str_replace(' ', '', $expire_date[1]),
					'cardnumber' => substr($_POST['wirecard-card-number'], 0, 6) . 'XXXXXXXX' . substr($_POST['wirecard-card-number'], -2),
					'createddate' =>date("Y-m-d h:i:s"), 
					'ipaddress' =>  helper::get_client_ip(),
					'status_code' => 1, //default başarısız
					'result_code' => '', 
					'result_message' => '',
					'mode' =>  $mode,
					'shared_payment_url' => 'null'
				);

		
			if ($mode == 'form')
			{
			
				$request = new CCProxySaleRequest();
				$request->ServiceType = "CCProxy";
				$request->OperationType = "Sale";
				$request->Token= new Token();
				$request->Token->UserCode=$this->wirecard_usercode;
				$request->Token->Pin=$this->wirecard_pin;
				$request->MPAY = $order_id;
				$request->IPAddress = helper::get_client_ip();  
				$request->PaymentContent = "Odeme"; //Ürünisimleri
				$request->InstallmentCount = $_POST["wirecard-installment-count"] == is_null ? 0 :  $_POST["wirecard-installment-count"];
				$request->Description = "";
				$request->ExtraParam = "";
				$request->CreditCardInfo= new CreditCardInfo();
				$request->CreditCardInfo->CreditCardNo= str_replace(' ', '', $_POST['wirecard-card-number']);
				$request->CreditCardInfo->OwnerName= $_POST['wirecard-card-name'];
				$request->CreditCardInfo->ExpireYear=str_replace(' ', '', $expire_date[1]);
				$request->CreditCardInfo->ExpireMonth = str_replace(' ', '', $expire_date[0]);
				$request->CreditCardInfo->Cvv=$_POST['wirecard-card-cvc'];
				$request->CreditCardInfo->Price=$amount * 100;  // 1 TL için 100 Gönderilmeli.
			
				$record['shared_payment_url']='null';
				try {
				
					$response = CCProxySaleRequest::execute($request); 
					
				} catch (Exception $e) {
					$record['result_code'] = 'ERROR';
					$record['result_message'] = $e->getMessage();
					$record['status_code'] = 1;
					return $record;
				}

				$sxml = new SimpleXMLElement( $response);

				$record['status_code'] = $sxml->Item[0]['Value'];
				$record['result_code'] = $sxml->Item[1]['Value'];
				$record['result_message'] = helper::turkishreplace( $sxml->Item[2]['Value']);
				$record['wirecard_id'] =   $sxml->Item[3]['Value'];
				$record['cardnumber'] =    $sxml->Item[5]['Value'];
				$record['amount_paid'] = (string) $response['StatusCode'] == "0" ? $amount : 0;
			
				return $record;

			}
			elseif ($mode =='shared3d') //shared 3d ortak ödeme sayfası 3d 
			{
 			
				$request = new WDTicketPaymentFormRequest();
				$request->ServiceType = "WDTicket";
				$request->OperationType = "Sale3DSURLProxy";
				$request->Token= new Token();
				$request->Token->UserCode=$this->wirecard_usercode;
				$request->Token->Pin=$this->wirecard_pin;
				$request->MPAY = $order_id;
				$request->PaymentContent = "Odeme"; //Ürünisimleri
				$request->PaymentTypeId = "1";
				$request->Description = "";
				$request->ExtraParam = "";
				$request->ErrorURL =  $order->get_checkout_payment_url(true);
				$request->SuccessURL = $order->get_checkout_payment_url(true);
				$request->Price = $amount * 100;  // 1 TL için 100 Gönderilmeli.

				try {
				
					$response = WDTicketPaymentFormRequest::Execute($request); 
				
				} catch (Exception $e) {
			
					$record['result_code'] = 'ERROR';
					$record['result_message'] = $e->getMessage();
					$record['status_code'] = 1;
					return $record;
				}
	
				$sxml = new SimpleXMLElement( $response);
			
				$record['status_code'] = $sxml->Item[0]['Value'];
				$record['result_code'] = $sxml->Item[1]['Value'];
				$record['result_message'] = helper::turkishreplace( $sxml->Item[2]['Value']);
				$record['shared_payment_url'] =$sxml->Item[3]['Value'];

				return $record;

			}
			else 
			{ //shared
				$request = new WDTicketPaymentFormRequest();
				$request->ServiceType = "WDTicket";
				$request->OperationType = "SaleURLProxy";
				$request->Token= new Token();
				$request->Token->UserCode=$this->wirecard_usercode;
				$request->Token->Pin=$this->wirecard_pin;
				$request->MPAY = $order_id;
				$request->PaymentContent = "Odeme"; //Ürünisimleri
				$request->PaymentTypeId = "1";
				$request->Description = "";
				$request->ExtraParam = "";
				$request->ErrorURL =  $order->get_checkout_payment_url(true);
				$request->SuccessURL = $order->get_checkout_payment_url(true);
				$request->Price = $amount * 100;  // 1 TL için 100 Gönderilmeli.
	

				try {
				
					$response = WDTicketPaymentFormRequest::Execute($request); 
				
				} catch (Exception $e) {
			
					$record['result_code'] = 'ERROR';
					$record['result_message'] = $e->getMessage();
					$record['status_code'] = 1;
					return $record;
				}
	
				$sxml = new SimpleXMLElement( $response);
			
				$record['status_code'] = $sxml->Item[0]['Value'];
				$record['result_code'] = $sxml->Item[1]['Value'];
				$record['result_message'] = helper::turkishreplace( $sxml->Item[2]['Value']);
				$record['shared_payment_url'] =$sxml->Item[3]['Value'];
				
				return $record;
			}
			
		}

		
		function receipt_page($orderid)
		{
			global $woocommerce;
			$error_message = false;
			$order = new WC_Order($orderid);
			$cc_form_key = $this->createSecret($orderid);
			$status = $order->get_status();
			
			if($status != 'pending')
			{
				return 'ok';
			}				
	
			if(isset($_POST['cc_form_key']) AND $_POST['cc_form_key'] == $this->createSecret($orderid) AND !isset($_POST['MPAY'])) { //form ile direk ödeme
			
				$record = $this->pay($orderid);
				if($record["shared_payment_url"] != 'null') // Ortak ödemeye yönlen 
				{	
					$this->saveRecord($record);
					wp_redirect($record["shared_payment_url"]);
					exit;
				}
				$this->saveRecord($record);
				if($record['status_code'] == 0 ) {//Başarılı işlem
			
					$order->update_status('processing', __('Processing wirecard payment', 'woocommerce'));
					$order->add_order_note('Ödeme wirecard ile tamamlandı. İşlem no: #' . $record['wirecard_id'] . ' Tutar ' . $record['amount'] . ' Taksit: ' . $record['installment'] . ' Ödenen:' . $record['amount_paid']);
					$order->payment_complete();
					$woocommerce->cart->empty_cart(); 
					wp_redirect($this->get_return_url()); 
					exit;
					$error_message = false;
				}
				else { //Başarısız işlem
					$order->update_status('pending', 'Ödeme Doğrulanamadı, tekrar deneyiniz. ', 'woocommerce');
					$error_message = 'Ödeme başarısız oldu: Bankanızın cevabı: ('. $record['result_code'] . ') ' . $record['result_message'];
				}				
			}	
			elseif (isset($_POST['MPAY'])) { //Ortak ödemeden gelirse. 
			
				$record = $this->getRecordByOrderId($_POST['MPAY']);
				$record['status_code'] = $_POST['StatusCode'];
				$record['result_code'] = $_POST['ResultCode'];
				$record['result_message'] =$_POST['ResultMessage'];		
				$this->saveRecord($record);	
					if($record['status_code'] == 0 ) {//Başarılı işlem
					
					$order->update_status('processing', __('Processing wirecard payment', 'woocommerce'));
					$order->add_order_note('Ödeme wirecard posu ile tamamlandı. İşlem no: #' . $record['wirecard_id'] . ' Tutar ' . $record['amount'] . ' Taksit: ' . $record['installment'] . ' Ödenen:' . $record['amount_paid']);
					$order->payment_complete();
					$woocommerce->cart->empty_cart(); 
					wp_redirect($this->get_return_url());
					$this->saveRecord($record);
					exit;
					$error_message = false;
				}
				else { //Başarısız işlem7
				
					$order->update_status('pending', 'Ödeme Doğrulanamadı, tekrar deneyiniz. ', 'woocommerce');
					$error_message = 'Ödeme başarısız oldu: Kart Bankası İşlem Cevabı: ('. $record['result_code'] . ') ' . $record['result_message'];
				}
			}
	
			include(dirname(__FILE__).'/payform.php');
		}
	
		private function addRecord($record)
		{
	
			global $wpdb;
			$wpdb->insert($wpdb->prefix . 'wirecard', $record);
		}


		private function updateRecordByOrderId($record)
		{
			global $wpdb;
			$wpdb->update($wpdb->prefix . 'wirecard', $record, array('order_id' => (int) $record['order_id']));
		}
		public function saveRecord($record)
		{		
			if (isset($record['order_id'])
					AND $record['order_id']
					AND $this->getRecordByOrderId($record['order_id']))
				return $this->updateRecordByOrderId($record);
			return $this->addRecord($record);
		}

		public function getRecordByOrderId($order_id)
		{
			global $wpdb;
			return $wpdb->get_row('SELECT * FROM `' . $wpdb->prefix . 'wirecard` WHERE `order_id` = ' . (int) $order_id, ARRAY_A);
		}
	}

	//END Class wirecard

	function wirecard($methods)
	{
		$methods[] = 'wirecard';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'wirecard');


}
