<?php
namespace AmazonPay;

/* Interface for HttpCurl.php */

interface HttpCurlInterface
{    
    /* Set Http header for Access token for the GetUserInfo call */
    
    public function setHttpHeader();
    
    /* Setter for  Access token to get the user info */
    
    public function setAccessToken($accesstoken);
    
    /* POST using curl for the following situations
     * 1. API calls
     * 2. IPN certificate retrieval
     * 3. Get User Info
     */
    
    public function httpPost($url, $userAgent = null, $parameters = null);
    
    /* GET using curl for the following situations
     * 1. IPN certificate retrieval
     * 3. Get User Info
     */
    
    public function httpGet($url, $userAgent = null);
}
