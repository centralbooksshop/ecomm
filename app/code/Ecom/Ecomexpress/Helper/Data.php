<?php

namespace Ecom\Ecomexpress\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	public function execute_curl($url,$type,$params) {
		try {		
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_FAILONERROR,1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);			
			if ($type == 'post'){				
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			}			
			$retValue = curl_exec($ch);
			//print_r($retValue);die('----');
			if ($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				echo "cURL error ({$errno}):\n {$error_message}";
			}
			if (!$retValue)
			{
				return false;
			}			
			curl_close($ch);
			return $retValue;	
		}
		catch(Exception $e)
		{
			return	$e->getMessage();
		}
	}
}