<?php

namespace AmazonPay;


require_once 'HttpCurlInterface.php';

/**
 * Handles Curl POST function for all requests
 */
class HttpCurl implements HttpCurlInterface
{
    private $config = array();
    private $header = false;
    private $accessToken = null;
    private $curlResponseInfo = null;

    /**
     * Takes user configuration array as input
     * Takes configuration for API call or IPN config
     *
     * @param null $config
     */
    public function __construct($config = null)
    {
        $this->config = $config;
    }

    /* Setter for boolean header to get the user info */

    public function setHttpHeader()
    {
        $this->header = true;
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken($accesstoken)
    {
        $this->accessToken = $accesstoken;
    }

    /**
     * Add the common Curl Parameters to the curl handler $ch
     * Also checks for optional parameters if provided in the config
     * config['cabundle_file']
     * config['proxy_port']
     * config['proxy_host']
     * config['proxy_username']
     * config['proxy_password']
     *
     * @param $url
     * @param $userAgent
     *
     * @return resource
     */
    private function commonCurlParams($url, $userAgent)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!is_null($this->config['cabundle_file'])) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->config['cabundle_file']);
        }

        if (!empty($userAgent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }

        if ($this->config['proxy_host'] != null && $this->config['proxy_port'] != -1) {
            curl_setopt($ch, CURLOPT_PROXY, $this->config['proxy_host'].':'.$this->config['proxy_port']);
        }

        if ($this->config['proxy_username'] != null && $this->config['proxy_password'] != null) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config['proxy_username'].':'.$this->config['proxy_password']);
        }

        return $ch;
    }

    /**
     * @inheritdoc
     */
    public function httpPost($url, $userAgent = null, $parameters = null)
    {
        $ch = $this->commonCurlParams($url, $userAgent);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HEADER, false);

        return $this->execute($ch);
    }

    /**
     * @inheritdoc
     */
    public function httpGet($url, $userAgent = null)
    {
        $ch = $this->commonCurlParams($url, $userAgent);

        // Setting the HTTP header with the Access Token only for Getting user info
        if ($this->header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: bearer '.$this->accessToken,
            ));
        }

        return $this->execute($ch);
    }

    /**
     * Execute Curl request
     *
     * @param $ch
     *
     * @return mixed|string
     * @throws \Exception
     */
    private function execute($ch)
    {
        $response = '';

        // Ensure we never send the "Expect: 100-continue" header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        $response = curl_exec($ch);
        if ($response === false) {
            $error_msg = 'Unable to post request, underlying exception of '.curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        } else {
            $this->curlResponseInfo = curl_getinfo($ch);
        }
        curl_close($ch);

        return $response;
    }

    /**
     * Get the output of Curl Getinfo
     *
     * @return null
     */
    public function getCurlResponseInfo()
    {
        return $this->curlResponseInfo;
    }
}
