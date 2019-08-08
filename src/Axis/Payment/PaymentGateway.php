<?php

namespace Axis\Payment;

use Illuminate\Http\Request;

class PaymentGateway extends Utility
{

    private $ecnKey;
    private $secureSecret;
    private $merchantCode;
    private $merchantid;
    private $url;
    private $return_url;
    private $version;


    public function __construct($config)
    {
        $this->config = $config;
        $this->ecnKey = $config['ecn_key'];
        $this->secureSecret = $config['secure_secret'];
        $this->merchantCode = $config['merchant_access_code'];
        $this->merchantid = $config['merchant_id'];
        $this->url = $config['gateway_url'];
        $this->return_url = $config['redirect_url'];
        $this->version = $config['version'];
    }

    public function makePayment($data = array())
    {
        $data['vpc_Version'] = $this->version;
        $data['vpc_AccessCode'] = $this->merchantCode;
        $data['vpc_MerchantId'] = $this->merchantid;
        $data['vpc_ReturnURL'] = $this->return_url;

        unset($data["inprocess"]);
        ksort($data);

        $str = $this->secureSecret;
        $dataToPostToPG = "";
        foreach ($data as $key => $val) {
            $str = $str . $val;
            $dataToPostToPG .= $key . "=" . $val . "::";
        }

        $pos = strrpos($dataToPostToPG, "::");
        if ($pos !== false) {
            $dataToPostToPG = substr_replace($dataToPostToPG, "", $pos, strlen("::"));
        }

        $SecureHash = hash('sha256', utf8_encode($str));
        $dataToPostToPG = "vpc_SecureHash=" . $SecureHash . "::" . $dataToPostToPG;

        $ciphertext_base64 = $this->encrypt($dataToPostToPG, $this->ecnKey);

        return '<form name="payment_server_request" action="' . $this->url . '" method="post" accept-charset="ISO-8859-1" align="center">
        <input type="hidden" name="vpc_MerchantId" id="vpc_MerchantId" value="' . $this->merchantid . '" > 							
        <input type="hidden" name="EncData" id="EncData" value="' . $ciphertext_base64 . '" > 
        </form><script type="text/javascript">document.payment_server_request.submit(); </script>';
    }

    public function checkPaymentResponse(Request $request)
    {

        $received_data = $request->all();
        if (!$received_data || $received_data['EncDataResp'] == "") {
            return array('success' => true, 'message' => "No response from PG");
        }

        $ciphertext_dec = "";

        $ciphertext_base64 = $received_data['EncDataResp'];

        $ciphertext_dec = $this->decrypt($ciphertext_base64, $this->ecnKey);
        // remove last occurrence of ::
        $pos = strrpos($ciphertext_dec, "::");
        if ($pos !== false) {
            $ciphertext_dec = substr_replace($ciphertext_dec, "", $pos, strlen("::"));
        }

        $array_data_string = "";
        $array_data_string = explode("::", $ciphertext_dec);

        $origial_array = array();
        if ($array_data_string) {
            foreach ($array_data_string as  $value) {
                $temp_array = explode("||", $value);
                $origial_array[$temp_array[0]] = $temp_array[1];
            }
        }

        // Get the hash sent by PG 
        $received_hash = $origial_array["vpc_SecureHash"];
        unset($origial_array["vpc_SecureHash"]);

        //Calculate hash of parameters received from PG
        ksort($origial_array);
        if ($origial_array) {
            $str = $this->secureSecret;
            foreach ($origial_array as $key => $val) {
                $str = $str . $val;
            }
        }

        $Cal_hash = hash('sha256', utf8_encode($str));

        $origial_array = $this->parseDigitalReceipt($origial_array);
        $authorised = $this->validate_hash($Cal_hash, $received_hash);
        /*     switch ($origial_array['vpc_TxnResponseCode']){
            case "Aborted":
                $vpc_TxnResponseCode_desc = "Transaction Aborted";
                break;
            case "0":
                $vpc_TxnResponseCode_desc= "No Value Returned";
                break;
            }
 */

        return array('authorized' => $authorised, 'response' => $origial_array);
    }

    private function parseDigitalReceipt($origial_array)
    {
        $vpc_CSCResultCode = $this->null2unknown("vpc_CSCResultCode", $origial_array);
        $vpc_VerStatus = $this->null2unknown("vpc_VerStatus", $origial_array);
        $dReceipt = array(
            "vpc_Version" => $this->null2unknown("vpc_Version", $origial_array),
            "vpc_Command" => $this->null2unknown("vpc_Command", $origial_array),
            "vpc_MerchTxnRef" => $this->null2unknown("vpc_MerchTxnRef", $origial_array),
            "vpc_Merchant" => $this->null2unknown("vpc_Merchant", $origial_array),
            "vpc_TxnResponseCode" => $this->null2unknown("vpc_TxnResponseCode", $origial_array),
            "vpc_AcqResponseCode" => $this->null2unknown("vpc_AcqResponseCode", $origial_array),
            "vpc_Message" => $this->null2unknown("vpc_Message", $origial_array),
            "vpc_Locale" => $this->null2unknown("vpc_Locale", $origial_array),
            "vpc_Amount" => $this->null2unknown("vpc_Amount", $origial_array),
            "vpc_OrderInfo" => $this->null2unknown("vpc_OrderInfo", $origial_array),
            "vpc_ReceiptNo" => $this->null2unknown("vpc_ReceiptNo", $origial_array),
            "vpc_Card" => $this->null2unknown("vpc_Card", $origial_array),
            "vpc_TransactionNo" => $this->null2unknown("vpc_TransactionNo", $origial_array),
            "vpc_BatchNo" => $this->null2unknown("vpc_BatchNo", $origial_array),
            "vpc_AuthorizeId" => $this->null2unknown("vpc_AuthorizeId", $origial_array),
            "vpc_VerSecurityLevel" => $this->null2unknown("vpc_VerSecurityLevel", $origial_array),
            "vpc_3DSXID" => $this->null2unknown("vpc_3DSXID", $origial_array),
            "vpc_3DSECI" => $this->null2unknown("vpc_3DSECI", $origial_array),
            "vpc_VerToken" => $this->null2unknown("vpc_VerToken", $origial_array),
            "vpc_3DSenrolled" => $this->null2unknown("vpc_3DSenrolled", $origial_array),
            "vpc_3DSstatus" => $this->null2unknown("vpc_3DSstatus", $origial_array),
            "vpc_VerStatus" => $vpc_VerStatus,
            "vpc_VerType" => $this->null2unknown("vpc_VerType", $origial_array),
            "vpc_Currency" => $this->null2unknown("vpc_Currency", $origial_array),
            "vpc_AcqCSCRespCode" => $this->null2unknown("vpc_AcqCSCRespCode", $origial_array),
            "vpc_CSCResultCode" =>  $vpc_CSCResultCode,
            "vpc_TxnResponseCode_desc" => "0",
            "vpc_CSCResultCode_desc" => $this->displayCSCResponse($vpc_CSCResultCode),
            "vpc_CSCRequestCode" => $this->null2unknown("vpc_CSCRequestCode", $origial_array),
            "vpc_VerStatus_desc" => $this->getStatusDescription($vpc_VerStatus, $vpc_CSCResultCode)

        );
        return $dReceipt;
    }
}
