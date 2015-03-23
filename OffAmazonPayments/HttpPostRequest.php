<?php

/* class HttpCurl
 * Handles Curl POST function for all requests
 */
class HttpCurl
{
    private $_url = null;
    private $_response;
    private $_config = array();
    private $_header = false;
    private $_accessToken = null;
    
    /* Takes user configuration array as input
     * Takes configuration for API call or IPN config
     */
    public function __construct($config = null)
    {
        $this->_config = $config;
        
    }
    
    /* Getter for response of the curl POST
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /* Setter for boolean header to get the user info
     */
    public function setHttpHeader()
    {
        $this->_header = true;
    }
    
    /* Setter for  Access token to get the user info
     */
    public function setAccessToken($accesstoken)
    {
        $this->_accessToken = $accesstoken;
    }
    
    /* POST using curl for the following situations
     * 1. API calls
     * 2. IPN certificate retrieval
     * 3. Get User Info
     */
    public function _httpPost($url, $userAgent = null, $parameters = null)
    {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // if a ca bundle is configured, use it as opposed to the default ca
        // configured for the server
        
        if (!is_null($this->_config['cabundle_file'])) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->_config['cabundle_file']);
        }
        
        if (!empty($userAgent))
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        
        /*
         * setting the HTTP header with the Access Token only for Getting user info
         */
        if ($this->_header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: bearer ' . $this->_accessToken
            ));
        }
        
        if (!empty($parameters)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        
        if ($this->_config['proxy_host'] != null && $this->_config['proxy_port'] != -1) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_config['proxy_host'] . ':' . $this->_config['proxy_port']);
        }
        
        if ($this->_config['proxy_username'] != null && $this->_config['proxy_password'] != null) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_config['proxy_username'] . ':' . $this->_config['proxy_password']);
        }
        
        $response = '';
        if (!$response = curl_exec($ch)) {
            $error_msg = "Unable to post request, underlying exception of " . curl_error($ch);
            curl_close($ch);
            throw new Exception($error_msg);
        }
        
        curl_close($ch);
        $this->_response = $response;
    }
}