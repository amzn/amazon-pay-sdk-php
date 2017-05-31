<?php
namespace AmazonPay;

class Signature
{
    const MWS_VERSION = '2013-01-01';
    private $config = array();
    private $signature = null;
    
    private $mwsEndpointPath = null;
    private $mwsEndpointUrl = null;
    private $modePath = null;
    
    private $mwsServiceUrl = null;
    
    private $mwsServiceUrls = array('eu' => 'mws-eu.amazonservices.com',
				     'na' => 'mws.amazonservices.com',
				     'jp' => 'mws.amazonservices.jp');
    
     private $regionMappings = array('de' => 'eu',
				     'uk' => 'eu',
				     'us' => 'na',
				     'jp' => 'jp');
    
    public function __construct($config = array(),$parameters = array())
    {
        $config = array_change_key_case($config, CASE_LOWER);
        $this->config = $config;
        $this->signature = $this->calculateSignature($parameters);
    }
    
    public function getSignature()
    {
	return trim($this->signature);
    }
    
    /* Create an Array of required parameters, sort them
     * Calculate signature and invoke the POST them to the MWS Service URL
     *
     * @param AWSAccessKeyId [String]
     * @param Version [String]
     * @param SignatureMethod [String]
     * @param Timestamp [String]
     * @param Signature [String]
     */
    
    private function calculateSignature($parameters)
    {
	$this->createServiceUrl();
	$signature = $this->signParameters($parameters);
	return $signature;
    }
    
    /* Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * If Signature Version is 0, it signs concatenated Action and Timestamp
     *
     * If Signature Version is 1, it performs the following:
     *
     * Sorts all  parameters (including SignatureVersion and excluding Signature,
     * the value of which is being created), ignoring case.
     *
     * Iterate over the sorted list and append the parameter name (in original case)
     * and then its value. It will not URL-encode the parameter values before
     * constructing this string. There are no separators.
     *
     * If Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    
    private function signParameters(array $parameters)
    {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm        = "HmacSHA1";
        $stringToSign     = null;
        if (2 === $signatureVersion) {
            $algorithm                     = "HmacSHA256";
            $parameters['SignatureMethod'] = $algorithm;
            $stringToSign                  = $this->calculateStringToSignV2($parameters);
        } else {
            throw new \Exception("Invalid Signature Version specified");
        }
        
        return $this->sign($stringToSign, $algorithm);
    }
    
    /* Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    
    private function calculateStringToSignV2(array $parameters)
    {
        $data = 'POST';
        $data .= "\n";
        $data .= $this->mwsEndpointUrl;
        $data .= "\n";
        $data .= $this->mwsEndpointPath;
        $data .= "\n";
        $data .= $this->getParametersAsString($parameters);
        return $data;
    }
    
    /* Convert paremeters to Url encoded query string */
    
    private function getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->urlEncode($value);
        }
        
        return implode('&', $queryParameters);
    }
    
    private function urlEncode($value)
    {
        return str_replace('%7E', '~', rawurlencode($value));
    }
    
    /* Computes RFC 2104-compliant HMAC signature.*/
    
    private function sign($data, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new \Exception("Non-supported signing method specified");
        }
        
        return base64_encode(hash_hmac($hash, $data, $this->config['secret_key'], true));
    }
    
    /* Formats date as ISO 8601 timestamp */
    
    private function getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    private function createServiceUrl()
    {
        $this->modePath = strtolower($this->config['sandbox']) ? 'OffAmazonPayments_Sandbox' : 'OffAmazonPayments';
        
        if (!empty($this->config['region'])) {
            $region = strtolower($this->config['region']);
            if (array_key_exists($region, $this->regionMappings)) {
                $this->mwsEndpointUrl  = $this->mwsServiceUrls[$this->regionMappings[$region]];
                $this->mwsServiceUrl   = 'https://' . $this->mwsEndpointUrl . '/' . $this->modePath . '/' . self::MWS_VERSION;
                $this->mwsEndpointPath = '/' . $this->modePath . '/' . self::MWS_VERSION;
            } else {
                throw new \Exception($region . ' is not a valid region');
            }
        } else {
            throw new \Exception("config['region'] is a required parameter and is not set");
        }
    }
}
