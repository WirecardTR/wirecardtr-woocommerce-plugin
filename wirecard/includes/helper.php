<?php
class Helper {


    //Guid oluşturmak için kullanılan metottur.
	public static function GUID() {
		if (function_exists ( 'com_create_guid' ) === true) {
			return trim ( com_create_guid (), '{}' );
		}
		
		return sprintf ( '%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand ( 0, 65535 ), mt_rand ( 0, 65535 ), mt_rand ( 0, 65535 ), mt_rand ( 16384, 20479 ), mt_rand ( 32768, 49151 ), mt_rand ( 0, 65535 ), mt_rand ( 0, 65535 ), mt_rand ( 0, 65535 ) );
	}
	//Client ipsine ulaşmamızı sağlayan kısımdır.
	public static function get_client_ip() {
		if (getenv ( 'HTTP_CLIENT_IP' ))
			$ipaddress = getenv ( 'HTTP_CLIENT_IP' );
		else if (getenv ( 'HTTP_X_FORWARDED_FOR' ))
			$ipaddress = getenv ( 'HTTP_X_FORWARDED_FOR' );
		else if (getenv ( 'HTTP_X_FORWARDED' ))
			$ipaddress = getenv ( 'HTTP_X_FORWARDED' );
		else if (getenv ( 'HTTP_FORWARDED_FOR' ))
			$ipaddress = getenv ( 'HTTP_FORWARDED_FOR' );
		else if (getenv ( 'HTTP_FORWARDED' ))
			$ipaddress = getenv ( 'HTTP_FORWARDED' );
		else if (getenv ( 'REMOTE_ADDR' ))
			$ipaddress = getenv ( 'REMOTE_ADDR' );
		else
			$ipaddress = '127.0.0.1';
		
		return $ipaddress;
	}

	/**
	 * Xml çıktısı oluşturmamıza olanak sağlayan metod.
	 */
	public static function formattoXMLOutput($input_xml) {
		$doc = new DOMDocument ();
		$doc->loadXML ( $input_xml );
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$output = $doc->saveXML ();
		return $output;
	}
	/**
	 * Geçerli url yolunu üretir.
	 * @method "request scheme" + "://" + "server name" + "server port"
	 * @return string
	 */
	public static function getCurrentUrl() {
		return "http://".$_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI']."";
	}
	
	public static function turkishreplace($sData){
 
		$newphrase=$sData;
		$newphrase = str_replace("ÃƒÅ“","U",$newphrase);
		$newphrase = str_replace("Ã…Âž","S",$newphrase);
		$newphrase = str_replace("Ã„Âž","G",$newphrase);
		$newphrase = str_replace("Ãƒâ€¡","C",$newphrase);
		$newphrase = str_replace("Ã„Â°","I",$newphrase);
		$newphrase = str_replace("Ãƒâ€“","O",$newphrase);
		$newphrase = str_replace("ÃƒÂ¼","u",$newphrase);
		$newphrase = str_replace("Ã…Å¸","s",$newphrase);
		$newphrase = str_replace("ÃƒÂ§","c",$newphrase);
		$newphrase = str_replace("Ã„Â±","i",$newphrase);
		$newphrase = str_replace("ÃƒÂ¶","o",$newphrase);
		$newphrase = str_replace("Ã„Å¸","g",$newphrase);
	 
		$newphrase = str_replace("Ãœ","U;",$newphrase);
		$newphrase = str_replace("Åž","S",$newphrase);
		$newphrase = str_replace("Äž","G",$newphrase);
		$newphrase = str_replace("Ã‡","C",$newphrase);
		$newphrase = str_replace("Ä°","I",$newphrase);
		$newphrase = str_replace("Ã–","O",$newphrase);
		$newphrase = str_replace("Ã¼","u",$newphrase);
		$newphrase = str_replace("ÅŸ","s",$newphrase);
		$newphrase = str_replace("Ã§","c",$newphrase);
		$newphrase = str_replace("Ä±","i",$newphrase);
		$newphrase = str_replace("Ã¶","o",$newphrase);
		$newphrase = str_replace("ÄŸ","g",$newphrase);
	 
		$newphrase = str_replace("%u015F","s",$newphrase);
		$newphrase = str_replace("%E7","c",$newphrase);
		$newphrase = str_replace("%FC","u",$newphrase);
		$newphrase = str_replace("%u0131","i",$newphrase);
		$newphrase = str_replace("%F6","o",$newphrase);
		$newphrase = str_replace("%u015E","S",$newphrase);
		$newphrase = str_replace("%C7","C",$newphrase);
		$newphrase = str_replace("%DC","U",$newphrase);
		$newphrase = str_replace("%D6","O",$newphrase);
		$newphrase = str_replace("%u0130","I",$newphrase);
		$newphrase = str_replace("%u011F","g",$newphrase);
		$newphrase = str_replace("%u011E","G",$newphrase);
		$newphrase = str_replace("Å","s",$newphrase);
		$newphrase = str_replace("Ä","g",$newphrase);
		
	
	 
	return $newphrase;
	}

}

?>
