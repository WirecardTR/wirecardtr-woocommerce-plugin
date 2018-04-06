<?php include('menu.php');?>
<h2>CCProxySale</h2>
<br/>
<fieldset>
    <legend><label style="font-weight:bold;width:250px;">Proxy Sale Bilgileri</label></legend>
    <label style="font-weight:bold;">Servis Adı &nbsp; :   &nbsp; </label> CCProxy<br>
    <label style="font-weight:bold;">Operasyon Adı &nbsp; :&nbsp; </label> Sale <br>
    <label style="font-weight:bold;">UserCode  &nbsp;:  &nbsp;</label> Wirecard tarafından verilen değer <br>
    <label style="font-weight:bold;">Pin &nbsp;:  &nbsp;</label> Wirecard tarafından verilen değer <br>
    <label style="font-weight:bold;">Fiyat &nbsp;:  &nbsp;</label> 0,01 TL <br>
    <label style="font-weight:bold;">MPAY &nbsp;:  &nbsp;</label> Benzersiz işlem numarası. Bu parametre işlem sonucunda aynen bize geri gönderilir. <br>
    <label style="font-weight:bold;">Ipaddress &nbsp;:  &nbsp;</label>İşlem yapan kullanıcının ip adresi<br>
    <label style="font-weight:bold;">İşlem İçeriği &nbsp;:  &nbsp;</label>Bilgisayar Ödemesi <br>
    <label style="font-weight:bold;">Açıklama &nbsp;:  &nbsp;</label>Ödeme işleminin tanımı <br>
    <label style="font-weight:bold;">Ekstra Parametre &nbsp;:  &nbsp;</label>Bu alanın boş olarak gönderilmesi gerekmektedir. <br>
    
     
</fieldset>


<form action="" method="post" class="form-horizontal">
    <fieldset>
        <!-- Form Name -->
        <legend><label style="font-weight:bold;width:250px;">Kart Bilgileriyle Ödeme</label></legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="">Kart Sahibi Adı Soyadı:</label>
            <div class="col-md-4">
                <input value="Ahmet Yılmaz" name="ownerName" class="form-control input-md">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" for="">  Kart Numarası:</label>
            <div class="col-md-4">
                <input value="4282209004348015" name="creditCardNo" class="form-control input-md">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" for="">  Son Kullanma Tarihi Ay/Yıl: </label>
            <div class="col-md-4">
                <input value="12" name="expireMonth" class="form-control input-md" width="50px">
                <input value="2022" name="expireYear" class="form-control input-md" width="50px">
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" for="">  CVC: </label>
            <div class="col-md-4">
                <input value="123" name="cvv" class="form-control input-md">
            </div>

        </div> 
         <div class="form-group">
            <label class="col-md-4 control-label" for="">  Taksit Sayısı: </label>
            <div class="col-md-4">
                
                <select name="installmentCount">
                    <option value="0">Peşin</option>
                    <option value="3">3</option>
                    <option value="6">6</option>
                    <option value="9">9</option>

                </select>
            </div>
        </div>
    </fieldset>
    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label" for=""></label>
        <div class="col-md-4">
            <button type="submit" id="" name="" class="btn btn-danger"> Ödeme Yap</button>
        </div>
    </div>
</form>
<?php if (!empty($_POST)): ?>
<?php
    /**
     * Setting ayarlarını settings sınıfı içerisinden alıyoruz.
     * Token bilgilerini ve CCProxySaleRequest sınıfı parametrelerini formdan gelen bilgilerle doldurup, xml servis çağrısını başlatıyoruz.
     * Xml Servis çağrısı sonucunda oluşan servis çıktısını ekrana xml formatında yazdırıyoruz.
     */
    $settings=new Settings();
    $request = new CCProxySaleRequest();
	$request->ServiceType = "CCProxy";
    $request->OperationType = "Sale";

    $request->Token= new Token();
    $request->Token->UserCode=$settings->UserCode;
    $request->Token->Pin=$settings->Pin;
    
    $request->MPAY = "";
    $request->IPAddress = helper::get_client_ip();  
    $request->PaymentContent = "Bilgisayar";
    $request->InstallmentCount = $_POST["installmentCount"];
    $request->Description = "BLGSYR01";
    $request->ExtraParam = "";

    $request->CreditCardInfo= new CreditCardInfo();
    $request->CreditCardInfo->CreditCardNo=$_POST["creditCardNo"];
    $request->CreditCardInfo->OwnerName=$_POST["ownerName"];
    $request->CreditCardInfo->ExpireYear=$_POST["expireYear"];
    $request->CreditCardInfo->ExpireMonth=$_POST["expireMonth"];
    $request->CreditCardInfo->Cvv=$_POST["cvv"];
    $request->CreditCardInfo->Price=1;//0.01 TL

    $response = CCProxySaleRequest::execute($request); // CCProxySale servisi başlatılması için gerekli servis çağırısını temsil eder.
    print "<h3>Sonuç:</h3>";
	echo "<pre>";
    echo htmlspecialchars ($response);  
    echo "</pre>";
	?>

<?php endif; ?>	  

<?php include('footer.php');?>