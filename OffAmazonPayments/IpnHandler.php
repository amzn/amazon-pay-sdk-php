<?php
/**
 * IPN_Handler 
 *
 * This file is invoked whenever a new notification needs to be processed,
 * and will call the IPN API
 *
 * Note that if the IPN Client throws an Exception, the IPH_Handler routine is
 * expected to throw a HTTP error response to signal that there has been an issue
 * with the message
 * 
 * This class logs information to an error logs, 
 * and places the last received notification
 * into the session context as a way to pass to other pages
 *
 */

class IpnHandler
{
    
    /**
     * Expected value for the CN field in an
     * Amazon issued certificate
     */
    private $_headers = null;
    
    /**
     * IHttpRequestFactory for creating http requests
     *
     */
    private $_body = null;
    private $_snsmessage = null;
    private $fields = array();
    private $_signatureFields = array();
    private $_Certificate = null;
    private $ExpectedCnName = 'sns.amazonaws.com';
    
    private $_IpnConfig = array('caBundleFile'  => null,
                                'ProxyHost'     => null,
                                'ProxyPort'     => -1,
                                'ProxyUsername' => null,
                                'ProxyPassword' => null);
    
    /**
     * Create a new instance of the openssl implementation of
     * verify signature
     * 
     * @param string expectedCnName for Amazon cert
     * @param IHttpRequestFactory httpRequestFactory factory to create http requests
     *
     * @return void
     */
    public function __construct($headers, $body, $IpnConfig = null)
    {
        $this->_headers = $headers;
        $this->_body    = $body;
        
        if($IpnConfig!=null){
            $this->_IpnConfig = $IpnConfig;
        }
        
        // get the list of fields that we are interested in
        $this->fields = array(
            "Timestamp" => true,
            "Message" => true,
            "MessageId" => true,
            "Subject" => false,
            "TopicArn" => true,
            "Type" => true
        );
        $this->parseRawMessage();
    }
    
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_IpnConfig))
        {
            $this->_IpnConfig[$name] = $value;
        }
        else
        {
            throw new Exception("Key " . $name . " is not part of the configuration", 1);
        }
    }
    
    public function __get($name)
    {
        if (array_key_exists($name, $this->_IpnConfig))
        {
            return $this->_IpnConfig[$name];
        }
        else
        {
            throw new Exception("Key " . $name . " was not found in the configuration", 1);
        }
    }
    
    /**
     * Converts a http POST body and headers into
     * a notification object
     * 
     * @param array  $headers post request headers
     * @param string $body    post request body, should be json
     * 
     * @throws Exception
     * 
     * @return OffAmazonPaymentNotifications_Notification 
     */
    private function parseRawMessage()
    {
        $this->_validateHeaders();
        $this->GetMessage();
        $this->_checkForCorrectMessageType();
        $this->verifySignatureIsCorrect();
        
    }
    
    public function GetJsonIpnMessage()
    {
        return json_decode($this->_snsmessage['Message'], true);
    }
    
    
    public function GetIpnMessageArray()
    {
        $IpnMessage = json_decode($this->_snsmessage['Message'], true);
        $IpnMessageToArray = simplexml_load_string((string) $IpnMessage['NotificationData']);
        $IpnMessageToArray = json_encode($IpnMessageToArray);
        $IpnMessageToArray = json_decode($IpnMessageToArray, true);
        
        return $IpnMessageToArray;
    }
    
    private function _validateHeaders()
    {
        // Quickly check that this is a sns message
        if (!array_key_exists('x-amz-sns-message-type', $this->_headers))
        {
            throw new Exception("Error with message - header " . "does not contain x-amz-sns-message-type header");
        }
        
        if ($this->_headers['x-amz-sns-message-type'] !== 'Notification')
        {
            throw new Exception("Error with message - header x-amz-sns-message-type is not " . "Notification, is " . $this->_headers['x-amz-sns-message-type']);
        }
    }
    
    private function GetMessage()
    {
        $this->_snsmessage = json_decode($this->_body, true);
        
        $json_error = json_last_error();
        
        if ($json_error != 0)
        {
            $errorMsg = "Error with message - content is not in json format" . $this->_getErrorMessageForJsonError($json_error) . " " . $json;
            throw new Exception($errorMsg);
        }
    }
    
    /**
     * Convert a json error code to a descriptive error
     * message
     * 
     * @param int $json_error message code
     * 
     * @return string error message
     */
    private function _getErrorMessageForJsonError($json_error)
    {
        switch ($json_error)
        {
            case JSON_ERROR_DEPTH:
                return " - maximum stack depth exceeded.";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return " - invalid or malformed JSON.";
                break;
            case JSON_ERROR_CTRL_CHAR:
                return " - control character error.";
                break;
            case JSON_ERROR_SYNTAX:
                return " - syntax error.";
                break;
            default:
                return ".";
                break;
        }
    }
    
    private function _checkForCorrectMessageType()
    {
        $type = $this->GetMandatoryField("Type");
        if (strcasecmp($type, "Notification") != 0)
        {
            throw new Exception("Error with SNS Notification - unexpected message with Type of " . $type);
        }
        
        if (strcmp($this->GetMandatoryField("Type"), "Notification") != 0)
        {
            throw new Exception("Error with signature verification - unable to verify " . $this->GetMandatoryField("Type") . " message");
        }
        else
        {
            
            // sort the fields into byte order based on the key name(A-Za-z)
            ksort($this->fields);
            
            // extract the key value pairs and sort in byte order
            $signatureFields = array();
            foreach ($this->fields as $fieldName => $mandatoryField)
            {
                if ($mandatoryField)
                {
                    $value = $this->GetMandatoryField($fieldName);
                }
                else
                {
                    $value = $this->GetField($fieldName);
                }
                
                if (!is_null($value))
                {
                    array_push($signatureFields, $fieldName);
                    array_push($signatureFields, $value);
                }
            }
            
            // create the signature string - key / value in byte order
            // delimited by newline character + ending with a new line character
            $this->_signatureFields = implode("\n", $signatureFields) . "\n";
            
        }
        
        
    }
    
    /**
     * Verify that the signature is correct for the given data and
     * public key
     * 
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificatePath path to certificate, can be file or url
     * 
     * @throws Exception if there 
     *                                                                is an error 
     *                                                                with the call
     * 
     * @return bool true if valid
     */
    public function verifySignatureIsCorrect()
    {
        
        $signature       = base64_decode($this->GetMandatoryField("Signature"));
        $certificatePath = $this->GetMandatoryField("SigningCertURL");
        
        $this->_Certificate = $this->GetCertificate($certificatePath);
        
        $result = $this->verifySignatureIsCorrectFromCertificate($signature);
        if (!$result)
        {
            throw new Exception("Unable to match signature from remote server: signature of " . $this->GetCertificate($certificatePath) . " , SigningCertURL of " . $this->GetMandatoryField("SigningCertURL") . " , SignatureOf " . $this->GetMandatoryField("Signature"));
        }
    }
    
    
    private function GetCertificate($certificatePath)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_URL, $certificatePath);
        
        // curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        
        if (!is_null($this->_IpnConfig['caBundleFile']))
        {
            curl_setopt($ch, CURLOPT_CAINFO, $this->_IpnConfig['caBundleFile']);
        }
        
        if ($this->_IpnConfig['ProxyHost'] != null && $this->_IpnConfig['ProxyPort'] != -1)
        {
            curl_setopt($ch, CURLOPT_PROXY, $this->_IpnConfig['ProxyHost'] . ':' . $this->_IpnConfig['ProxyPort']);
        }
        
        if ($this->_IpnConfig['ProxyUsername'] != null && $this->_IpnConfig['ProxyPassword'] != null)
        {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_IpnConfig['ProxyUsername'] . ':' . $this->_IpnConfig['ProxyPassword']);
        }
       
        $response = '';
        if (!$response = curl_exec($ch))
        {
            $errorNo = curl_error($ch);
            curl_close($ch);
            throw new Exception($errorNo);
        }
        
        
        
        curl_close($ch);
        return $response;
        
    }
    
    /**
     * Verify that the signature is correct for the given data and
     * public key
     * 
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificate     certificate object defined in Certificate.php
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if there 
     *                                                                is an error 
     *                                                                with the call
     * 
     * @return bool true if valid
     */
    public function verifySignatureIsCorrectFromCertificate($signature)
    {
        $certKey = openssl_get_publickey($this->_Certificate);
        
        if ($certKey === False)
        {
            throw new Exception("Unable to extract public key from cert");
        }
        
        try
        {
            $certInfo    = openssl_x509_parse($this->_Certificate, true);
            $certSubject = $certInfo["subject"];
            
            if (is_null($certSubject))
            {
                throw new Exception("Error with certificate - subject cannot be found");
            }
        }
        catch (Exception $ex)
        {
            throw new Exception("Unable to verify certificate - error with the certificate subject", null, $ex);
        }
        
        if (strcmp($certSubject["CN"], $this->ExpectedCnName))
        {
            throw new Exception("Unable to verify certificate issued by Amazon - error with certificate subject");
        }
        
        $result = -1;
        try
        {
            $result = openssl_verify($this->_signatureFields, $signature, $certKey, OPENSSL_ALGO_SHA1);
        }
        catch (Exception $ex)
        {
            throw new Exception("Unable to verify signature - error with the verification algorithm", null, $ex);
        }
        
        return ($result > 0);
    }
    
    
    /**
     * Extract the mandatory field from the message and return the contents
     * 
     * @param string $fieldName name of the field to extract
     * 
     * @throws Exception if not found
     * 
     * @return string field contents if found
     */
    public function GetMandatoryField($fieldName)
    {
        $value = $this->GetField($fieldName);
        if (is_null($value))
        {
            throw new Exception("Error with json message - mandatory field " . $fieldName . " cannot be found");
        }
        return $value;
    }
    
    /**
     * Extract the field if present, return null if not defined
     * 
     * @param string $fieldName name of the field to extract
     * 
     * @return string field contents if found, null otherwise
     */
    public function GetField($fieldName)
    {
        if (array_key_exists($fieldName, $this->_snsmessage))
        {
            return $this->_snsmessage[$fieldName];
        }
        else
        {
            return null;
        }
    }
}