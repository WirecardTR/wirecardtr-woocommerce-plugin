<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 
Class wirecardConfig
{

	const max_installment = 9;

	public $max_installment = 9;
	public $id_order = false;
	public $id_customer = false;
	public $id_invoice = false;
	public $id_cart = false;
	public $id_bank = false;
	public $cc = array(
		'name' => '',
		'no' => '',
		'cvv' => '',
		'expire_year' => '',
		'expire_month' => '',
	);
	public $amount = 0.00;
	public $cart_amount = 0.00;
	public $total_products = 0.00;
	public $total_shipping = 0.00;
	public $total_tax = 0.00;
	public $installment = 0;
	public $customer = array(
		'firstname' => '',
		'lastname' => '',
		'birthday' => '',
		'address' => '',
		'city' => '',
		'country' => '',
		'zip' => '',
		'phone' => '',
		'mobile' => '',
		'email' => '',
	);
	public $items = array(
		'products' => array(),
		'shipping' => false
	);
	public $bank = false;

	public function __construct()
	{
		add_action('admin_init', array($this, 'register_all_ins'));
	}

	public static function getAvailablePrograms()
	{
		return array(
			'axess' => array('name' => 'Axess', 'bank' => 'Akbank A.Ş.'),
			'word' => array('name' => 'WordCard', 'bank' => 'Yapı Kredi Bankası'),
			'bonus' => array('name' => 'BonusCard', 'bank' => 'Garanti Bankası A.Ş.'),
			'cardfinans' => array('name' => 'CardFinans', 'bank' => 'FinansBank A.Ş.'),
			'asyacard' => array('name' => 'AysaCard', 'bank' => 'BankAsya A.Ş.'),
			'maximum' => array('name' => 'Maximum', 'bank' => 'T.C. İş Bankası'),
			'paraf' => array('name' => 'Paraf', 'bank' => 'T Halk Bankası A.Ş.'),
		);
	}

	public static function setRatesFromPost($posted_data)
	{
		$banks = wirecardConfig::getAvailablePrograms();
		$return = array();
		foreach ($banks as $k => $v) {
			$return[$k] = array();
			for ($i = 1; $i <= self::max_installment; $i++) {
				$return[$k][$i] = isset($posted_data[$k]['installments'][$i]) ? ((float) $posted_data[$k]['installments'][$i]) : 0.0;
				if ($posted_data[$k]['installments'][$i]['passive']) {
					$return[$k][$i] = -1.0;
				}
			}
		}
		return $return;
	}

	public static function setRatesDefault()
	{
		$banks = wirecardConfig::getAvailablePrograms();
		$return = array();
		foreach ($banks as $k => $v) {
			$return[$k] = array();
			for ($i = 1; $i <= self::max_installment; $i++) {
				$return[$k]['installments'][$i] = (float) (1 + $i + ($i / 5) + 0.1);
				if ($i == 1)
					$return[$k]['installments'][$i] = 0.00;
			}
		}
		return $return;
	}

	public function register_all_ins()
	{
		if (isset($_POST['wirecard_rates']))
			update_option('wirecard_rates', $_POST['wirecard_rates']);
	}

	public static function createRatesUpdateForm($rates)
	{
		$wirecard_url = plugins_url().'/wirecard/';
		$return = '<table class="wirecard_table table">'
				. '<thead>'
				. '<tr><th>Banka</th>';
		for ($i = 1; $i <= self::max_installment; $i++) {
			$return .= '<th>' . $i . ' taksit</th>';
		}
		$return .= '</tr></thead><tbody>';

		$banks = wirecardConfig::getAvailablePrograms();
		foreach ($banks as $k => $v) {
			$return .= '<tr>'
					. '<th text-align="left"><img src="' . $wirecard_url .'img/banks/'. $k . '.png" width="120px"></th>';
			for ($i = 1; $i <= self::max_installment; $i++) {
				$return .= '<td><input type="number" step="0.001" maxlength="4" size="4" '
						. ' value="' . ((float) $rates[$k]['installments'][$i]) . '"'
						. ' name="wirecard_rates[' . $k . '][installments][' . $i . ']"/></td>';
			}
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}

	public static function calculatePrices($price, $rates)
	{
		$banks = wirecardConfig::getAvailablePrograms();
		$return = array();
		foreach ($banks as $k => $v) {
			$return[$k] = array();
			for ($i = 1; $i <= self::max_installment; $i++) {
				$return[$k]['installments'][$i] = array(
					'total' => number_format((((100 + $rates[$k]['installments'][$i]) * $price) / 100), 2, '.', ''),
					'monthly' => number_format((((100 + $rates[$k]['installments'][$i]) * $price) / 100) / $i, 2, '.', ''),
				);
			}
		}
		return $return;
	}

	public function getRotatedRates($price, $rates)
	{
		$prices = wirecardConfig::calculatePrices($price, $rates);
		for ($i = 1; $i <= self::max_installment; $i++) {
			
		}
	}

	public static function createInstallmentsForm($price, $rates)
	{
		$wirecard_url = plugins_url().'/wirecard/';
		$prices = wirecardConfig::calculatePrices($price, $rates);
		$return = '<table class="wirecard_table table installments">'
				. '<thead>'
				. '<tr><th>Banka</th>';
		for ($i = 1; $i <= self::max_installment; $i++) {
			$return .= '<th>' . $i . ' taksit</th>';
		}
		$return .= '</tr></thead><tbody>';

		$banks = wirecardConfig::getAvailablePrograms();
		foreach ($banks as $k => $v) {
			$return .= '<tr>'
					. '<th><img src="'.$wirecard_url.'img/banks/' . $k . '.png"></th>';
			for ($i = 1; $i <= self::max_installment; $i++) {
				$return .= '<td><input type="number" step="0.001" maxlength="4" size="4" '
						. ' value="' . ((float) $rates[$k]['installments'][$i]) . '"'
						. ' name="wirecard_rates[' . $k . '][installments][' . $i . ']"/></td>';
			}
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}

	public static function frontInstallmentsForm($price, $rates)
	{
		$prices = wirecardConfig::calculatePrices($price, $rates);
		$return = '<table class="wirecard_table table">'
				. '<thead>'
				. '<tr>';
		$banks = wirecardConfig::getAvailablePrograms();
		$return .= '<th  style="width:90px;">Taksit</th>';
		foreach ($banks as $k => $v) {
			$return .= '<th><img src="' . get_site_url() . '/wp-content/plugins/wirecard/img/banks/' . $k . '.png" style="margin:3px;"></th>';
		}
		$return .= '</tr></thead><tbody>';

		for ($i = 1; $i <= self::max_installment; $i++) {
			$return .= '<tr class="ins"><td><input type="radio"' . ' value="' . $i . '"'
					. ' name="wirecard_selected_installment"/>' . $i . '<small> Taksit</small></td>';
			foreach ($banks as $k => $v) {
				$rate = $rates[$k]['installments'][$i];
				$total = round((((float) $rate / 100) * $price) + $price, 2);
				$return .= '<td>' . $total . ' TL</td>';
			}
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}

	public static function getProductInstallments($price, $rates)
	{
		//print_r($rates);
		$prices = wirecardConfig::calculatePrices($price, $rates);
		$banks = wirecardConfig::getAvailablePrograms();
		$return = '
		<link rel="stylesheet" type="text/css" href="' . get_site_url() . '/wp-content/plugins/wirecard/css/product_tab.css" />
		<section class="page-product-box"><h3 class="page-product-heading">Taksit Seçenekleri</h3><div class="row">';
		$bank_counter = 0;
		foreach ($banks as $k => $v) {
			$bank_counter++;
			if ($bank_counter == 5) {
				$return .= '</div><div class="row">';
			}
			$return .= '<div class="wirecard_bank">
				<div class="box">
					<div class="block_title" align="center"><img src="' . get_site_url() . '/wp-content/plugins/wirecard/img/banks/' . $k . '.png"></div>';
			$return .= '<table class="table">
						<tr>
							<th>Taksit</th>
							<th>Aylık </th>
							<th>Toplam</th>
						</tr>';
			for ($i = 1; $i <= 9; $i++) {
				$rate = $rates[$k]['installments'][$i];
				$total = round((((float) $rate / 100) * $price) + $price, 2);
				$monthly = round(($total / $i), 2);
				$return .= '<tr>
					<td>' . $i . '</td>
					<td class="' . $k . '">' . $monthly . get_woocommerce_currency_symbol() . '</td>
					<td>' . $total . get_woocommerce_currency_symbol() . '</td>
				</tr>';
			}
			$return .= '</table></div></div>';
		}
		$return .= '<div class="wirecard_bank">
				<div class="box">
					<div class="block_title"><h3>Diğer Kartlar</h3></div>
					Tüm bankaların kartları ile visa/mastercard/amex tek çekim (taksitsiz) ödeme yapabilirsiniz.
					<hr/>
					<img class="col-sm-12" src="' . get_site_url() . '/wp-content/plugins/wirecard/banks/cards.png"/>
					</div>
					</div>';

		$return .= '</div></section>';
		return $return;
	}

}
