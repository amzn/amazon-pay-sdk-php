<?php
/**
 * Class IPN_Handler
 * Takes headers and body of the IPN message as input in the constructor
 * verifies that the IPN is from the right resource and has the valid data
 */
require_once 'HttpPostRequest.php';
class IpnHandler
{

    private $_headers = null;
    private $_body = null;
    private $_snsMessage = null;
    private $_fields = array();
    private $_signatureFields = array();
    private $_certificate = null;
    private $_expectedCnName = 'sns.amazonaws.com';

    private $_ipnConfig = array('cabundle_file' => null,
                                'proxy_host' => null,
                                'proxy_port' => -1,
                                'proxy_username' => null,
                                'proxy_password' => null);


    public function __construct($headers, $body, $ipnConfig = null)
    {
        $this->_headers = array_change_key_case($headers, CASE_LOWER);
        $this->_body    = $body;

        if ($ipnConfig != null) {
            $this->_checkConfigKeys($ipnConfig);
        }

        // get the list of fields that we are interested in
        $this->_fields = array(
            "Timestamp" => true,
            "Message" => true,
            "MessageId" => true,
            "Subject" => false,
            "TopicArn" => true,
            "Type" => true
        );

        //validate the IPN message header [x-amz-sns-message-type]
        $this->_validateHeaders();

        //converts the IPN [Message] to Notification object
        $this->_getMessage();

        //checks if the notification [Type] is Notification and constructs the signature fields
        $this->_checkForCorrectMessageType();

        //verifies the signature against the provided pem file in the IPN
        $this->_constructAndVerifySignature();
    }

    private function _checkConfigKeys($ipnConfig)
    {
        $ipnConfig = array_change_key_case($ipnConfig, CASE_LOWER);

        foreach ($ipnConfig as $key => $value) {
            if (array_key_exists($key, $this->_ipnConfig)) {
                $this->_ipnConfig[$key] = $value;
            } else {
                throw new Exception('Key ' . $key . ' is either not part of the configuration or has incorrect Key name.
				check the _ipnConfig array key names to match your key names of your config array ', 1);
            }
        }
    }

    /* Setter function
     * sets the value for the key if the key exists in _ipnConfig
     */
    public function __set($name, $value)
    {
        if (array_key_exists(strtolower($name), $this->_ipnConfig)) {
            $this->_ipnConfig[$name] = $value;
        } else {
            throw new Exception("Key " . $name . " is not part of the configuration", 1);
        }
    }

    /* Getter function
     * returns the value for the key if the key exists in _ipnConfig
     */
    public function __get($name)
    {
        if (array_key_exists(strtolower($name), $this->_ipnConfig)) {
            return $this->_ipnConfig[$name];
        } else {
            throw new Exception("Key " . $name . " was not found in the configuration", 1);
        }
    }


    private function _validateHeaders()
    {
        // Quickly check that this is a sns message
        if (!array_key_exists('x-amz-sns-message-type', $this->_headers)) {
            throw new Exception("Error with message - header " . "does not contain x-amz-sns-message-type header");
        }

        if ($this->_headers['x-amz-sns-message-type'] !== 'Notification') {
            throw new Exception("Error with message - header x-amz-sns-message-type is not " . "Notification, is " . $this->_headers['x-amz-sns-message-type']);
        }
    }

    private function _getMessage()
    {
        $this->_snsMessage = json_decode($this->_body, true);

        $json_error = json_last_error();

        if ($json_error != 0) {
            $errorMsg = "Error with message - content is not in json format" . $this->_getErrorMessageForJsonError($json_error) . " " . $this->_snsMessage;
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
        switch ($json_error) {
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

    /* _checkForCorrectMessageType()
     *
     * Checks if the Field [Type] is set to ['Notification']
     * gets the value for the fields marked true in the fields array
     * constructs the signature string
     */

    private function _checkForCorrectMessageType()
    {
        $type = $this->_getMandatoryField("Type");
        if (strcasecmp($type, "Notification") != 0) {
            throw new Exception("Error with SNS Notification - unexpected message with Type of " . $type);
        }

        if (strcmp($this->_getMandatoryField("Type"), "Notification") != 0) {
            throw new Exception("Error with signature verification - unable to verify " . $this->_getMandatoryField("Type") . " message");
        } else {

            // sort the fields into byte order based on the key name(A-Za-z)
            ksort($this->_fields);

            // extract the key value pairs and sort in byte order
            $signatureFields = array();
            foreach ($this->_fields as $fieldName => $mandatoryField) {
                if ($mandatoryField) {
                    $value = $this->_getMandatoryField($fieldName);
                } else {
                    $value = $this->_getField($fieldName);
                }

                if (!is_null($value)) {
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
     * @throws Exception if there is an error  with the call
     *
     * @return bool true if valid
     */
    private function _constructAndVerifySignature()
    {

        $signature       = base64_decode($this->_getMandatoryField("Signature"));
        $certificatePath = $this->_getMandatoryField("SigningCertURL");

        $this->_certificate = $this->_getCertificate($certificatePath);

        $result = $this->verifySignatureIsCorrectFromCertificate($signature);
        if (!$result) {
            throw new Exception("Unable to match signature from remote server: signature of " . $this->_getCertificate($certificatePath) . " , SigningCertURL of " . $this->_getMandatoryField("SigningCertURL") . " , SignatureOf " . $this->_getMandatoryField("Signature"));
        }
    }

    /* _getCertificate($certificatePath)
     *
     * gets the certificate from the $certificatePath using Curl
     */
    private function _getCertificate($certificatePath)
    {
        $httpCurlRequest  = new HttpCurl($this->_ipnConfig);

	$httpCurlRequest->_httpGet($certificatePath);
	$response = $httpCurlRequest->getResponse();

        return $response;
    }

    /**
     * Verify that the signature is correct for the given data and
     * public key
     *
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificate     certificate object defined in Certificate.php
     */

    public function verifySignatureIsCorrectFromCertificate($signature)
    {
        $certKey = openssl_get_publickey($this->_certificate);

        if ($certKey === False) {
            throw new Exception("Unable to extract public key from cert");
        }

        try {
            $certInfo    = openssl_x509_parse($this->_certificate, true);
            $certSubject = $certInfo["subject"];

            if (is_null($certSubject)) {
                throw new Exception("Error with certificate - subject cannot be found");
            }
        }
        catch (Exception $ex) {
            throw new Exception("Unable to verify certificate - error with the certificate subject", null, $ex);
        }

        if (strcmp($certSubject["CN"], $this->_expectedCnName)) {
            throw new Exception("Unable to verify certificate issued by Amazon - error with certificate subject");
        }

        $result = -1;
        try {
            $result = openssl_verify($this->_signatureFields, $signature, $certKey, OPENSSL_ALGO_SHA1);
        }
        catch (Exception $ex) {
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
    public function _getMandatoryField($fieldName)
    {
        $value = $this->_getField($fieldName);
        if (is_null($value)) {
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
    public function _getField($fieldName)
    {
        if (array_key_exists($fieldName, $this->_snsMessage)) {
            return $this->_snsMessage[$fieldName];
        } else {
            return null;
        }
    }

    /* returnMessage() - JSON decode the raw [Message] portion of the IPN
     */
    public function returnMessage()
    {
        return json_decode($this->_snsMessage['Message'], true);
    }

    /* toJson() - Converts IPN [Message] field to JSON
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML notification data
     * @param remainingFields - consists of remaining IPN array fields that are merged
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in JSON format
     */
    public function toJson()
    {
        $response = $this->_simpleXmlObject();

        //Merging the remaining fields with the response
        $remainingFields = $this->_getRemainingIpnFields();
        $responseArray = array_merge($remainingFields,(array)$response);

        //Converting to JSON format
        $response = json_encode($responseArray);

        return $response;
    }


    /* toArray() - Converts IPN [Message] field to associative array
     * Merge the rema
     * @return response in array format
     */
    public function toArray()
    {
        $response = $this->_simpleXmlObject();

        //Converting the SimpleXMLElement Object to array()
        $response = json_encode($response);
        $response = json_decode($response, true);

        //Merging the remaining fields with the response array
        $remainingFields = $this->_getRemainingIpnFields();
        $response = array_merge($remainingFields,$response);

        return $response;
    }

    /* addRemainingFields() - Add remaining fileds to the datatype
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML response data
     * Convert to SimpleXML element object
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in array format
     */

    private function _simpleXmlObject()
    {
        $ipnMessage = $this->returnMessage();

        //Getting the Simple XML element object of the IPN XML Response Body
        $response = simplexml_load_string((string) $ipnMessage['NotificationData']);

        //Adding the Type,MessageId,TopicArn details of the IPN to the Simple XML elsement Object
        $response->addChild('Type', $this->_snsMessage['Type']);
        $response->addChild('MessageId', $this->_snsMessage['MessageId']);
        $response->addChild('TopicArn', $this->_snsMessage['TopicArn']);

        return $response;
    }

    /* _getRemainingIpnFields()
     * Gets the remaining fields of the IPN to be later appeded to the return message
     */
    private function _getRemainingIpnFields()
    {
        $ipnMessage = $this->returnMessage();

        $remainingFields = ['NotificationReferenceId' =>$ipnMessage['NotificationReferenceId'],
                            'NotificationType' =>$ipnMessage['NotificationType'],
                            'IsSample' =>$ipnMessage['IsSample'],
                            'SellerId' =>$ipnMessage['SellerId'],
                            'ReleaseEnvironment' =>$ipnMessage['ReleaseEnvironment'],
                            'Version' =>$ipnMessage['Version']];

        return $remainingFields;
    }
}