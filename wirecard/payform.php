<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 
$wirecard_plugin_url = plugins_url().'/wirecard';

$wirecard_settings = get_option("woocommerce_wirecard_settings");
$isInstallment = $wirecard_settings["installment"];
$mode = $wirecard_settings["mode"];

?>

<link rel="stylesheet" type="text/css" href="<?php echo $wirecard_plugin_url ?>/css/form.css" />
<script src="<?php echo $wirecard_plugin_url ?>/js/jquery-1.11.3.min.js"></script>
<script src="<?php echo $wirecard_plugin_url ?>/js/jquery.card.js"></script>
<script src="<?php echo $wirecard_plugin_url ?>/js/jquery.payment.min.js"></script>


<section>
 
    <?php 
    if($error_message) { ?>
		<div class="row">
            <ul class="woocommerce-error" id="errDiv">
                <li>Ödemeniz yapılamadı. Bankanızın cevabı: <br/> 
                <b><?php echo $error_message; ?></b><br/>
                Lütfen formu kontrol edip yeniden deneyiniz.
				</li>
            </ul>
        </div>
    <?php } ?>

       <h2 align="center">Kredi Kartı ile Ödeme </h2>
		
        <small> Ödemenizi bu sayfada kredi kartınız ile güvenli şekilde yapabilirsiniz. </small><br/>
        <hr/>
</section>
<form novalidate autocomplete="on" method="POST" id="cc_form" action="">

    <div class="row">
    <?php if($mode == 'form' || $mode == 'form3d') : ?>  
            <div class="wirecard_form_half">
                <table id="cc_form_inputs_table">
                    <tr>
                        <td>
                        Kart No <br/>
                    <input type="text" id="cc_number" name="wirecard-card-number" class="cc_input" placeholder="•••• •••• •••• ••••"/>
                    </td>
                    </tr>
                    <tr>
                    <td>
                        Son Kul. Tar.<br/>
                    <input type="text" size="5" id="cc_expiry" name="wirecard-card-expiry" class="cc_input" placeholder="AA/YY"/>
                    </td>
                    </tr>
                    <tr>
                <td>
                        Güvenlik kodu<br/>
                    <input type="text" size="4" id="cc_cvc" name="wirecard-card-cvc" class="cc_input" placeholder="•••"/>
                    </td>
                    </tr>
                    <tr>
                        <td >
                        Kart üzerindeki isim<br/>
                    <input type="text" id="cc_name" name="wirecard-card-name" class="cc_input" placeholder="Ad Soyad"/>
                    </td>
                    </tr>

                    <?php if($isInstallment == 'yes') : ?>
                    <tr>
                                <td >
                                Taksit Sayısı<br/>
                                <select name="wirecard-installment-count">
                                <option value="0">Peşin Ödeme</option>
                                <option value="3">3 Taksit</option>
                                <option value="6">6 Taksit</option>
                                <option value="9">9 Taksit</option>         
                                </select>
                            </td>
                            </tr>
                    <?php endif; ?>

                </table>

            </div>
      
            <div class="wirecard_form_half">
                <div class="card-wrapper"></div>
            </div>
            <div class="clear clearfix"></div>
                        <hr/>
            <div align="center">
            <div class="" id="cc_validation">Lütfen formu kontrol ediniz</div>
            </div>
     
     <?php endif; ?>

            <div align="center">
             <input type="hidden" name="cc_form_key" value="<?php echo $cc_form_key; ?>"/>
            <button type="submit" id="cc_form_submit" class="btn btn-lg btn-primary">Kredi Kartıyla Öde</button>
			</div>

    </div>
	
  
</form> 


<script>
    $('form#cc_form').card({
        // a selector or DOM element for the form where users will
        // be entering their information
        form: 'form#cc_form', // *required*
        // a selector or DOM element for the container
        // where you want the card to appear
		formSelectors: {
			numberInput: 'input#cc_number', // optional — default input[name="number"]
			expiryInput: 'input#cc_expiry', // optional — default input[name="expiry"]
			cvcInput: 'input#cc_cvc', // optional — default input[name="cvc"]
			nameInput: 'input#cc_name' // optional - defaults input[name="name"]
		},
		placeholders: {
		  number: '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;',
		  cvc: '&bull;&bull;&bull;',
		  expiry: 'AA/YY',
		  name: 'AD SOYAD'
		},
		messages: {
            monthYear: 'mm/yy' // optional - default 'month/year'
        },
        container: '.card-wrapper', // *required*
        width: "100%",
        formatting: true, // optional - default true
        // Default placeholders for rendered fields - optional
        // if true, will log helpful messages for setting up Card
        debug: true // optional - default false
    });

	$('table#cc_table tr').click(function() {
		$(this).find('td input:radio').prop('checked', true);
	})

    jQuery(function ($) {
        $('table#cc_form_table').removeClass('error success');
        $('input#cc_number').payment('formatCardNumber');
        $('input#cc_expiry').payment('formatCardExpiry');
        $('input#cc_cvc').payment('formatCardCVC');
        $("#cc_form_submit").attr("disabled", true);

        $('.cc_input').bind('keypress keyup keydown focus', function (e) {
            $(this).removeClass('error');
            $("#cc_form_submit").attr("disabled", true);
            var hasError = false;
            var cardType = $.payment.cardType($('input#cc_number').val());


            if (!$.payment.validateCardNumber($('input#cc_number').val())) {
                $('input#cc_number').addClass('error');
                hasError = 'number';
            }
            if (!$.payment.validateCardExpiry($('input#cc_expiry').payment('cardExpiryVal'))) {
                $('input#cc_expiry').addClass('error');
                hasError = 'expiry';
            }
            if (!$.payment.validateCardCVC($('input#cc_cvc').val(), cardType)) {
                $('input#cc_cvc').addClass('error');
                hasError = 'cvc';
            }
            if ($('input#cc_name').val().length < 3) {
                $('input#cc_name').addClass('error');
                hasError = 'name';
            }

            if (hasError === false) {
//                console.log(hasError);
                $("#cc_form_submit").removeAttr("disabled");
                $("#cc_validation").hide();
            }
            else {
                $("#cc_validation").show();
                $("#cc_form_submit").attr("disabled", true);
                $('table#cc_form_table').addClass('error');
            }
        });
		$('.cc_input').keypress();
    });
</script>
