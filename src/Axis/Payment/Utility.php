<?php
namespace Axis\Payment;

class Utility 
{	

	function encrypt($input, $key) 
	{		
	    return  base64_encode(openssl_encrypt($input,"AES-256-ECB", $key, OPENSSL_RAW_DATA ));
	}
	
	function decrypt($sStr, $key) 
	{		
		return  openssl_decrypt(base64_decode($sStr), 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
	}	
	
	function null2unknown($check_null,$Array_data)
	{
		if(!isset($Array_data[$check_null]))
		{
			return "No Value Returned";
		}
		else
		{
			return $Array_data[$check_null];
		}
	}
	
	function displayCSCResponse($vpc_CSCResultCode)
	{
		$result="";
		if($vpc_CSCResultCode!=null OR strlen($vpc_CSCResultCode)==0)
		{
			if(strcasecmp($vpc_CSCResultCode,"Unsupported") OR strcasecmp($vpc_CSCResultCode,"No Value Returned"))
			{
				$result="CSC not supported or there was no CSC data provided";
			}
			else 
			{
				$input=substr($vpc_CSCResultCode,1,1);
				switch($input)
				{
					case 'M' : $result = "Exact code match"; break;
					case 'S' : $result = "Merchant has indicated that CSC is not present on the card (MOTO situation)"; break;
					case 'P' : $result = "Code not processed"; break;
					case 'U' : $result = "Card issuer is not registered and/or certified"; break;
					case 'N' : $result = "Code invalid or not matched"; break;
					default  : $result = "Unable to be determined";
				}
			}
		}
		else
		{
			$result="Null Response";
		}
	return $result;
	}
	
	
	function getStatusDescription($vStatus, $vpc_CSCResultCode) 
	{
		$result = "";
		if ($vStatus != null && !$vStatus=="") 
		{
			if(strcasecmp($vpc_CSCResultCode,"Unsupported") OR strcasecmp($vpc_CSCResultCode,"No Value Returned"))
			{
				$result = "3DS not supported or there was no 3DS data provided";
			} 
			else 
			{
				$input = substr($vStatus,1,1);
				switch ($input)
				{
					case 'Y'  : $result = "The cardholder was successfully authenticated."; break;
					case 'E'  : $result = "The cardholder is not enrolled."; break;
					case 'N'  : $result = "The cardholder was not verified."; break;
					case 'U'  : $result = "The cardholder's Issuer was unable to authenticate due to some system error at the Issuer."; break;
					case 'F'  : $result = "There was an error in the format of the request from the merchant."; break;
					case 'A'  : $result = "Authentication of your Merchant ID and Password to the ACS Directory Failed."; break;
					case 'D'  : $result = "Error communicating with the Directory Server."; break;
					case 'C'  : $result = "The card type is not supported for authentication."; break;
					case 'S'  : $result = "The signature on the response received from the Issuer could not be validated."; break;
					case 'P'  : $result = "Error parsing input from Issuer."; break;
					case 'I'  : $result = "Internal Payment Server system error."; break;
					default   : $result = "Unable to be determined"; break;
				   }
			   }
		   } 
		   else 
		   {
				$result = "null response";
		   }
			return $result;
	}
	
	function validate_hash($sCal_hash,$sreceived_hash)
	{		
		$hashValid = 0;
		
		if($sCal_hash == $sreceived_hash)
		{	
			$hashValid = 1;
		}
		
		return  $hashValid;
	}
}
