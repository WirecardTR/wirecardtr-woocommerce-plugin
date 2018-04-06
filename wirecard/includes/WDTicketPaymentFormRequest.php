<?php


/**
 * Ortak Ödeme formu 3D secure ve 3D secure olmadan ödeme için gerekli olan alanların tanımlandığı sınıftır.
 * Bu sınıf içerisinde execute metodu ile servis çağrısı başlatılır.
 * Execute metodu içerisinde tanımlanan "toXmlString" metodu gerekli xml metninin oluşturulmasını sağlar.
 * Execute metodu içerisinde tanımlanan url adresine oluşturulan xml post edilir.
 */
class WDTicketPaymentFormRequest
{
    public  $ServiceType; 
    public  $OperationType; 
    public  $Price; 
    public  $Token; 
    public  $MPAY; 
    public  $Description; 
    public  $ErrorURL; 
    public  $SuccessURL; 
    public  $ExtraParam; 
    public  $PaymentContent; 
    public  $PaymentTypeId; 

    public static function Execute(WDTicketPaymentFormRequest $request)
    {
     
        return  restHttpCaller::post("https://www.wirecard.com.tr/SGate/Gate" , $request->toXmlString());
    }    
    
    //İstek sonucunda oluşan çıktının xml olarak gösterilmesini sağlar.
    public function toXmlString()
    {
       
        $xml_data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
        "<WIRECARD>\n" .
        "    <ServiceType>" . $this->ServiceType . "</ServiceType>\n" .
        "    <OperationType>" . $this->OperationType . "</OperationType>\n" .
        "    <Token>\n" .
        "    <UserCode>" .urlencode($this->Token->UserCode) . "</UserCode>\n" .
        "    <Pin>" .urlencode($this->Token->Pin) . "</Pin>\n" .
        "    </Token>\n" .
        "    <Price>" . $this->Price . "</Price>\n" .
        "    <MPAY>" . $this->MPAY . "</MPAY>\n" .
        "    <Description>" . $this->Description . "</Description>\n" .
        "    <ErrorURL>" . $this->ErrorURL . "</ErrorURL>\n" .
        "    <SuccessURL>" . $this->SuccessURL . "</SuccessURL>\n" .
        "    <ExtraParam>" . $this->ExtraParam . "</ExtraParam>\n" .
        "    <PaymentContent>" . $this->PaymentContent . "</PaymentContent>\n" .
        "    <PaymentTypeId>" . $this->PaymentTypeId . "</PaymentTypeId>\n" .
        "</WIRECARD>";
     
         return $xml_data;
    }
}