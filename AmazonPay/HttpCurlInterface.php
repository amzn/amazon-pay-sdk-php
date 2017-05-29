<?php

namespace AmazonPay;

interface HttpCurlInterface
{
    /**
     * Set Http header for Access token for the GetUserInfo call
     */
    public function setHttpHeader();

    /**
     * Setter for  Access token to get the user info
     * @param $accesstoken
     *
     * @return mixed
     */
    public function setAccessToken($accesstoken);

    /**
     * POST using curl for the following situations
     * 1. API calls
     * 2. IPN certificate retrieval
     * 3. Get User Info
     *
     * @param string $url
     * @param null   $userAgent
     * @param null   $parameters
     *
     * @return mixed
     */
    public function httpPost($url, $userAgent = null, $parameters = null);

    /**
     * GET using curl for the following situations
     * 1. IPN certificate retrieval
     * 3. Get User Info
     *
     * @param string $url
     * @param null   $userAgent
     *
     * @return mixed
     */
    public function httpGet($url, $userAgent = null);
}
