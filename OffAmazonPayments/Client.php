
<?php
/*******************************************************************************
 *  Copyright 2015 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */
require_once 'ResponseParser.php';

class OffAmazonPaymentsService_Client
{
    const MWS_CLIENT_VERSION = '2013-01-01';
    const SERVICE_VERSION = '2013-01-01';
    
    //construct User agent string based off of the application_name,application_version,PHP platform
    private $_userAgent = null;
    
    private $_mwsEndpointPath = null;
    private $_mwsEndpointUrl = null;
    private $_profileEndpoint = null;
    private $_config = array('merchant_id'	   => null,
			     'secret_key' 	   => null,
			     'access_key' 	   => null,
			     'region' 		   => 'na',
			     'currency_code' 	   => 'USD',
			     'sandbox' 		   => false,
			     'platform_id' 	   => null,
			     'cabundle_file' 	   => null,
			     'application_name'    => null,
			     'application_version' => null,
			     'proxy_host' 	   => null,
			     'proxy_port' 	   => -1,
			     'proxy_username' 	   => null,
			     'proxy_password' 	   => null,
			     'client_id' 	   => null,
			     'user_profile_region' => 'na',
			     'handle_throttle' 	   => true);
    private $_modePath = null;
    
    private $_mwsServiceUrl = null;
    
    private $_mwsServiceUrls = array('eu' => 'mws-eu.amazonservices.com',
				     'na' => 'mws.amazonservices.com',
				     'jp' => 'mws.amazonservices.jp');
    
    //Prooduction profile end points to get the user information
    private $_liveProfileEndpoint = array('uk' => 'https://api.amazon.co.uk',
					  'na' => 'https://api.amazon.com',
					  'us' => 'https://api.amazon.com',
					  'de' => 'https://api.amazon.de',
					  'jp' => 'https://api.amazon.co.jp');
    
    //sandbox profile end points to get the user information
    private $_sandboxProfileEndpoint = array('uk' => 'https://api.sandbox.amazon.co.uk',
					     'na' => 'https://api.sandbox.amazon.com',
					     'us' => 'https://api.sandbox.amazon.com',
					     'de' => 'https://api.sandbox.amazon.de',
					     'jp' => 'https://api.sandbox.amazon.co.jp');
    
    private $_regionMappings = array('de' => 'eu',
				     'na' => 'na',
				     'uk' => 'eu',
				     'us' => 'na',
				     'jp' => 'jp');
    private $_success = false;
    
    /* Takes user configuration array from the user as input
     * Takes JSON file path with configuration information as input
     * Validates the user configuation array against existing _config array
     */ 
    public function __construct($config = null)
    {
        if (!is_null($config)) {
            
            if (is_array($config)) {
                $configArray = $config;
            }elseif ((!is_array($config)) && file_exists($config)) {
                $jsonString  = file_get_contents($config);
                $configArray = json_decode($jsonString, true);
            } else {
                throw new Exception($config . ' is not a supported type or the JSON file is not found in the specified path' . PHP_EOL . 'Supported Input types are:' . PHP_EOL . '1.' . $config . ' can be a JSON file name containing config data in JSON format' . PHP_EOL . '2 ' . $config . ' can be a PHP associative array' . PHP_EOL . '3 ' . $config . ' can be a JSON string' . PHP_EOL);
            }
            if (is_array($configArray)) {
                $this->_checkConfigKeys($configArray);
            } else {
                throw new Exception($configArray . ' is of the incorrect type ' . gettype($configArray) . ' and should be of the type array');
            }
        }
    }
    
    /* Checks if the keys of the input configuration matches the keys in the _config array
     * if they match the values are taken else throws exception
     * strict case match is not performed
     */
    private function _checkConfigKeys($config)
    {
        $config = array_change_key_case($config, CASE_LOWER);
        
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->_config)) {
                $this->_config[$key] = $value;
            } else {
                throw new Exception('Key ' . $key . ' is either not part of the configuration or has incorrect Key name.
				check the _config array key names to match your key names of your config array ', 1);
            }
        }
    }
    
    /* Setter for sandbox
     * sets the boolean value for _config['sandbox'] variable
     */
    public function setSandbox($value)
    {
	if(is_bool($value)){
	    $this->_config['sandbox'] = $value;
	} else{
	throw new Exception($value . ' is of type ' . gettype($value) . ' and should be a boolean value ');
    }
    }
    
    /* Setter for Proxy
     * input $proxy [array]
     * @param $proxy['proxy_user_host'] - hostname for the proxy
     * @param $proxy['proxy_user_port'] - hostname for the proxy
     * @param $proxy['proxy_user_name'] - if your proxy requeired a username
     * @param $proxy['proxy_user_password'] - if your proxy requeired a passowrd
     */
    public function setProxy($proxy)
    {
	
	if(!empty($proxy['proxy_user_host']));
	$this->_config['proxy_user_host'] = $proxy['proxy_user_host'];
	
	if(!empty($proxy['proxy_user_port']))
	$this->_config['proxy_user_port'] = $proxy['proxy_user_port'];
	
	if(!empty($proxy['proxy_user_name']))
	$this->_config['proxy_user_name'] = $proxy['proxy_user_name'];
	
	if(!empty($proxy['proxy_user_password']))
	$this->_config['proxy_user_password'] = $proxy['proxy_user_password'];
    }
    
    /* Getter
     * Gets the value for the key if the key exists in _config
     */
    public function __get($name)
    {
	$nonGetterKeys = array('access_key',
			    'secret_key',
			    'proxy_username',
			    'proxy_password');
	if(in_array(strtolower($name),$nonGetterKeys))
	{
	    throw new Exception('The value for '. $name .' cannot be returned for security' );
	}
        if (array_key_exists(strtolower($name), $this->_config)) {
            return $this->_config[strtolower($name)];
        } else {
            throw new Exception('Key ' . $name . ' is either not a part of the configuration array _config or the' . $name . 'does not match the key name in the _config array', 1);
        }
    }
    
    /* GetUserInfo convenience funtion - Returns user's profile information from Amazon using the access token returned by the Button widget.
     * 
     * @see http://docs.developer.amazonservices.com/en_US/apa_guide/APAGuide_ObtainProfile.html
     * @param $access_token [String]
     * @param _config['user_profile_region'] [String]
     */
    public function getUserInfo($access_token)
    {
        //Get the correct Profile Endpoint URL based off the country/region provided in the _config['user_profile_region']
	
        if (!empty($this->_config['user_profile_region'])) {
            $this->_profileEndpointUrl();
        } else {
            throw new InvalidArgumentException('Profile Region is a required parameter and is not set.');
        }
        if (empty($access_token)) {
            throw new InvalidArgumentException('Access Token is a required parameter and is not set');
        }
        
	//to make sure double encoding doesn't occur decode first and encode again.
        $access_token = urldecode($access_token);
        
        $c = curl_init($this->_profileEndpoint . '/auth/o2/tokeninfo?access_token=' . urlencode($access_token));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        if (!$response = curl_exec($c)) {
            $error_msg = 'Unable to post request, underlying exception of ' . curl_error($c);
            curl_close($c);
            throw new Exception($error_msg);
        }
        curl_close($c);
        $data = json_decode($response);
        
        if ($data->aud != $this->_config['client_id']) {
            // the access token does not belong to us
            header('HTTP/1.1 404 Not Found');
            throw new Exception('The Access token entered is incorrect');
        }
        
        // exchange the access token for user profile
        $c = curl_init($this->_profileEndpoint . '/user/profile');
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'Authorization: bearer ' . $access_token
        ));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        if (!$response = curl_exec($c)) {
            $error_msg = 'Unable to post request, underlying exception of ' . curl_error($c);
            curl_close($c);
            throw new Exception($error_msg);
        }
        curl_close($c);
        $userInfo = json_decode($response, true);
        return $userInfo;
    }
    
    /* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetOrderReferenceDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['address_consent_token'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getOrderReferenceDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetOrderReferenceDetails';
	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
        
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	if (!empty($requestParameters['amazon_order_reference_id']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
        
        if (!empty($requestParameters['address_consent_token']))
            $parameters['AddressConsentToken'] = $requestParameters['address_consent_token'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
	$responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* SetOrderReferenceDetails API call - Sets order reference details such as the order total and a description for the order.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetOrderReferenceDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function setOrderReferenceDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetOrderReferenceDetails';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	
        if (!empty($requestParameters['amazon_order_reference_id']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
        
        if (!empty($requestParameters['amount']))
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $requestParameters['amount'];
        
        $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($this->_config['platform_id']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $this->_config['platform_id'];
        if (!empty($requestParameters['seller_note']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $requestParameters['seller_note'];
        if (!empty($requestParameters['seller_order_id']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $requestParameters['seller_order_id'];
        if (!empty($requestParameters['store_name']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $requestParameters['store_name'];
        if (!empty($requestParameters['custom_information']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $requestParameters['custom_information'];
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmOrderReference.html
     
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function confirmOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmOrderReference';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	   
        if (!empty($requestParameters['amazon_order_reference_id']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
	
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CancelOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['cancel_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function cancelOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CancelOrderReference';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($AmazonOrderReferenceId))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
        
        if (!empty($requestParameters['cancel_reason']))
            $parameters['CancelationReason'] = $requestParameters['cancel_reason'];
	
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* CloseOrderReferenceDetails API call - Confirms that an order reference has been fulfilled (fully or partially)
     * and that you do not expect to create any new authorizations on this order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    public function closeOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseOrderReference';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_order_reference_id']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
        
        if (!empty($requestParameters['closure_reason']))
            $parameters['ClosureReason'] = $requestParameters['closure_reason'];
	
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* CloseAuthorization API call - Closes an authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    public function closeAuthorization($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseAuthorization';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_authorization_id']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['amazon_authorization_id'];
        
        if (!empty($requestParameters['closure_reason']))
            $parameters['ClosureReason'] = $requestParameters['closure_reason'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* Authorize API call - Reserves a specified amount against the payment method(s) stored in the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Authorize.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['authorization_amount'] [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['authorization_reference_id'] [String]
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function authorize($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Authorize';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
        if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_order_reference_id']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['amazon_order_reference_id'];
        
        if (!empty($requestParameters['authorization_amount']))
            $parameters['AuthorizationAmount.Amount'] = $requestParameters['authorization_amount'];
        
        $parameters['AuthorizationAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($requestParameters['authorization_reference_id'])) {
            $parameters['AuthorizationReferenceId'] = $requestParameters['authorization_reference_id'];
        } else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
        }
        
        if (!empty($requestParameters['capture_now']))
            $parameters['CaptureNow'] = strtolower($requestParameters['capture_now']);
        
        if (!empty($requestParameters['seller_authorization_note']))
            $parameters['SellerAuthorizationNote'] = $requestParameters['seller_authorization_note'];
        
        if (!empty($requestParameters['transaction_timeout']))
            $parameters['TransactionTimeout'] = $requestParameters['transaction_timeout'];
        
        if (!empty($requestParameters['soft_descriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['soft_descriptor'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* Authorize API call - Returns the status of a particular authorization and the total amount captured on the authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetAuthorizationDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_authorization_id'] [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    public function getAuthorizationDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetAuthorizationDetails';
	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_authorization_id']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['amazon_authorization_id'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* Capture API call - Captures funds from an authorized payment instrument.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Capture.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @param requestParameters['capture_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters[capture_reference_id'] - [String]
     * @optional requestParameters['seller_capture_note'] - [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function capture($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Capture';
	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_authorization_id']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['amazon_authorization_id'];
        
        if (!empty($requestParameters['capture_amount']))
            $parameters['CaptureAmount.Amount'] = $requestParameters['capture_amount'];
        
        $parameters['CaptureAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($requestParameters['capture_reference_id'])) {
            $parameters['CaptureReferenceId'] = $requestParameters['capture_reference_id'];
        } else {
            $parameters['CaptureReferenceId'] = uniqid('C01_REF_');
        }
        
        if (!empty($requestParameters['seller_capture_note']))
            $parameters['SellerCaptureNote'] = $requestParameters['seller_capture_note'];
        
	if (!empty($requestParameters['soft_descriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['soft_descriptor'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetCaptureDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_capture_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    
    public function getCaptureDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetCaptureDetails';
	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
        
        if (!empty($requestParameters['amazon_capture_id']))
            $parameters['AmazonCaptureId'] = $requestParameters['amazon_capture_id'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* Refund API call - Refunds a previously captured amount.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Refund.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_capture_id'] - [String]
     * @param requestParameters['refund_reference_id'] - [String]
     * @param requestParameters['refund_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_refund_note'] [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function refund($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Refund';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_capture_id']))
            $parameters['AmazonCaptureId'] = $requestParameters['amazon_capture_id'];
        
        if (!empty($requestParameters['refund_reference_id'])) {
            $parameters['RefundReferenceId'] = $requestParameters['refund_reference_id'];
        } else {
            $parameters['RefundReferenceId'] = uniqid('R01_REF_');
        }
        
        if (!empty($requestParameters['refund_amount']))
            $parameters['RefundAmount.Amount'] = $requestParameters['refund_amount'];
        
        $parameters['RefundAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($requestParameters['seller_refund_note']))
            $parameters['SellerRefundNote'] = $requestParameters['seller_refund_note'];
        
	if (!empty($requestParameters['soft_descriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['soft_descriptor'];
	
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
	    
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* GetRefundDetails API call - Returns the status of a particular refund.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetRefundDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_refund_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    
    public function getRefundDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetRefundDetails';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_refund_id']))
            $parameters['AmazonRefundId'] = $requestParameters['amazon_refund_id'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* GetServiceStatus API Call - Returns the operational status of the Off-Amazon Payments API section
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetServiceStatus.html
     *
     * The GetServiceStatus operation returns the operational status of the Off-Amazon Payments API
     * section of Amazon Marketplace Web Service (Amazon MWS).
     * Status values are GREEN, GREEN_I, YELLOW, and RED.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    
    public function getServiceStatus($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetServiceStatus';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
	    
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* CreateOrderReferenceForId API Call - Creates an order reference for the given object
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CreateOrderReferenceForId.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['Id'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean]
     * @optional requestParameters['ConfirmNow'] - [Boolean]
     * @optional Amount [Float] (required when confirm_now is set to true)
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    
    public function createOrderReferenceForId($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CreateOrderReferenceForId';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['id']))
            $parameters['Id'] = $requestParameters['id'];
        
        if (!empty($requestParameters['inherit_shipping_address'])) {
            $parameters['InheritShippingAddress'] = strtolower($requestParameters['inherit_shipping_address']);
        } else {
            $parameters['InheritShippingAddress'] = true;
        }
        if (!empty($requestParameters['confirm_now'])) {
            $parameters['ConfirmNow'] = strtolower($requestParameters['confirm_now']);
        } else {
            $parameters['ConfirmNow'] = false;
        }
        if (!empty($requestParameters['amount']))
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $requestParameters['amount'];
        
        $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($this->_config['platform_id']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $this->_config['platform_id'];
        
	if (!empty($requestParameters['seller_note']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $requestParameters['seller_note'];
        
	if (!empty($requestParameters['seller_order_id']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $requestParameters['seller_order_id'];
        
	if (!empty($requestParameters['store_name']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $requestParameters['store_name'];
        
	if (!empty($requestParameters['custom_information']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $requestParameters['custom_information'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetBillingAgreementDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    
    public function getBillingAgreementDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetBillingAgreementDetails';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
        
        if (!empty($requestParameters['address_consent_token']))
            $parameters['AddressConsentToken'] = $requestParameters['address_consent_token'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* SetBillingAgreementDetails API call - Sets billing agreement details such as a description of the agreement and other information about the seller.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetBillingAgreementDetails.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_billing_agreement_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    
    public function setBillingAgreementDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetBillingAgreementDetails';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
        
        if (!empty($this->_config['platform_id']))
            $parameters['BillingAgreementAttributes.PlatformId'] = $this->_config['platform_id'];
       
        if (!empty($requestParameters['seller_note']))
            $parameters['BillingAgreementAttributes.SellerNote'] = $requestParameters['seller_note'];
       
        if (!empty($requestParameters['seller_billing_agreement_id']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId'] = $requestParameters['seller_billing_agreement_id'];
        
	if (!empty($requestParameters['custom_information']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation'] = $requestParameters['custom_information'];
        
	if (!empty($requestParameters['store_name']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName'] = $requestParameters['store_name'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* ConfirmBillingAgreement API Call - Confirms that the billing agreement is free of constraints and all required information has been set on the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function confirmBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmBillingAgreement';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* ValidateBillignAgreement API Call - Validates the status of the BillingAgreement object and the payment method associated with it.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ValidateBillignAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String] 
     */
    public function validateBillignAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ValidateBillingAgreement';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* AuthorizeOnBillingAgreement API call - Reserves a specified amount against the payment method(s) stored in the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_AuthorizeOnBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param AuthorizationReferenceId [String]
     * @param AuthorizationAmount [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] - Defaults to 0
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['soft_descriptor'] - - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean] - Defaults to true
     * @optional requestParameters['mws_auth_token'] - [String]  
     */
    public function authorizeOnBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'AuthorizeOnBillingAgreement';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
        
        if (!empty($requestParameters['authorization_reference_id'])) {
            $parameters['AuthorizationReferenceId'] = $requestParameters['authorization_reference_id'];
        } else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
        }
        
        if (!empty($requestParameters['authorization_amount']))
            $parameters['AuthorizationAmount.Amount'] = $requestParameters['authorization_amount'];
        
        $parameters['AuthorizationAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        
        if (!empty($requestParameters['seller_authorization_note']))
            $parameters['SellerAuthorizationNote'] = $requestParameters['seller_authorization_note'];
       
        if (!empty($requestParameters['transaction_timeout']))
            $parameters['TransactionTimeout'] = $requestParameters['transaction_timeout'];
       
        if (!empty($requestParameters['capture_now']))
            $parameters['CaptureNow'] = strtolower($requestParameters['capture_now']);
       
        if (!empty($requestParameters['soft_descriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['soft_descriptor'];
        
	if (!empty($requestParameters['seller_note']))
            $parameters['SellerNote'] = $requestParameters['seller_note'];
       
        if (!empty($this->_config['platform_id']))
            $parameters['PlatformId'] = $this->_config['platform_id'];
       
        if (!empty($requestParameters['custom_information']))
            $parameters['SellerOrderAttributes.CustomInformation'] = $requestParameters['custom_information'];
       
        if (!empty($requestParameters['seller_order_id']))
            $parameters['SellerOrderAttributes.SellerOrderId'] = $requestParameters['seller_order_id'];
       
        if (!empty($requestParameters['store_name']))
            $parameters['SellerOrderAttributes.StoreName'] = $requestParameters['store_name'];
       
        if (!empty($requestParameters['inherit_shipping_address'])) {
            $parameters['InheritShippingAddress'] = strtolower($requestParameters['inherit_shipping_address']);
        } else {
            $parameters['InheritShippingAddress'] = true;
        }
	
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    /* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String] 
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]  
     */
    public function CloseBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseBillingAgreement';
        $requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	
	if (!empty($requestParameters['merchant_id'])){
            $parameters['SellerId'] = $requestParameters['merchant_id'];
        }
	else{
	    $parameters['SellerId'] = $this->_config['merchant_id'];
	}
	    
        if (!empty($requestParameters['amazon_billing_agreement_id']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['amazon_billing_agreement_id'];
        
	if (!empty($requestParameters['closure_reason']))
            $parameters['ClosureReason'] = $requestParameters['closure_reason'];
	    
	if (!empty($requestParameters['mws_auth_token']))
            $parameters['MWSAuthToken'] = $requestParameters['mws_auth_token'];
        
	$response = $this->_calculateSignatureAndPost($parameters);
        $responseObject  = new ResponseParser($response);
        return ($responseObject);
    }
    
    public function Charge($requestParameters = null)
    {
	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	$OrderParameters = $requestParameters;
	
	$oro = false;
	$ba = false;
	
	$setParameters = $requestParameters;
	$authorizeParameters = $requestParameters;
	$confirmParameters = $requestParameters;
	
	if(!empty($OrderParameters['amazon_reference_id'])){
	    if(substr( $OrderParameters['amazon_reference_id'], 0, 3 ) === 'P01' || substr( $OrderParameters['amazon_reference_id'], 0, 3 ) ==='S01'){
		$oro = true;
		$setParameters['amazon_order_reference_id'] = $OrderParameters['amazon_reference_id'];
		$authorizeParameters['amazon_order_reference_id'] = $OrderParameters['amazon_reference_id'];
		$confirmParameters['amazon_order_reference_id'] = $OrderParameters['amazon_reference_id'];
	    }
	    elseif(substr( $OrderParameters['amazon_reference_id'], 0, 3 ) === 'B01' || substr( $OrderParameters['amazon_reference_id'], 0, 3 ) ==='C01'){
		$ba = true;
		$setParameters['amazon_billing_agreement_id'] = $OrderParameters['amazon_reference_id'];
		$authorizeParameters['amazon_billing_agreement_id'] = $OrderParameters['amazon_reference_id'];
		$confirmParameters['amazon_billing_agreement_id'] = $OrderParameters['amazon_reference_id'];
	    }
	}
	else{
	    throw new Exception('key amazon_reference_id is null and is a required parameter');
	}
	
	if(!empty($OrderParameters['charge_amount'])){
	    $setParameters['amount'] = $OrderParameters['charge_amount'];
	    $authorizeParameters['authorization_amount'] = $OrderParameters['charge_amount'];
	}
	if(!empty($OrderParameters['charge_note'])){
	    $setParameters['seller_note'] = $OrderParameters['charge_note'];
	    $authorizeParameters['seller_authorization_note'] = $OrderParameters['charge_note'];
	    $authorizeParameters['seller_note'] = $OrderParameters['charge_note'];
	}
	if(!empty($OrderParameters['charge_order_id'])){
	    $setParameters['seller_order_id'] = $OrderParameters['charge_order_id'];
	    $setParameters['seller_billing_agreement_id'] = $OrderParameters['charge_order_id'];
	    $authorizeParameters['seller_order_id'] = $OrderParameters['charge_order_id'];
	    
	}
	
	$authorizeParameters['capture_now'] = true;
	
	if($oro){
		$response = $this->setOrderReferenceDetails($setParameters);
	    
	    if($this->_success){
		$this->confirmOrderReference($confirmParameters);
	    }
	    if($this->_success){
		$response = $this->Authorize($authorizeParameters);
	    }
	    return $response;
	    
	}elseif($ba){
		$response = $this->SetBillingAgreementDetails($setParameters);
	    
	    if($this->_success){
		$response = $this->ConfirmBillingAgreement($confirmParameters);
	    }
	    if($this->_success){
		$response = $this->AuthorizeOnBillingAgreement($authorizeParameters);
	    }
	    return $response;
	}
	
	
    }
    
    /* Create an Array of required parameters, sort them
     * calculate signature and invoke the POST them to the MWS Service URL
     *
     * @param AWSAccessKeyId [String]
     * @param Version [String]
     * @param SignatureMethod [String]
     * @param Timestamp [String]
     * @param Signature [String]
     */
    private function _calculateSignatureAndPost($parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_config['access_key'];
        $parameters['Version']          = self::SERVICE_VERSION;
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->_getFormattedTimestamp();
        uksort($parameters, 'strcmp');
        
        $this->_createServiceUrl();
        
        $parameters['Signature'] = $this->_signParameters($parameters);
        $parameters              = $this->_getParametersAsString($parameters);
        $response                = $this->_invokePost($parameters);
        return $response;
    }
    
    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
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
    private function _signParameters(array $parameters)
    {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm        = "HmacSHA1";
        $stringToSign     = null;
        if (2 === $signatureVersion) {
            $algorithm                     = "HmacSHA256";
            $parameters['SignatureMethod'] = $algorithm;
            $stringToSign                  = $this->_calculateStringToSignV2($parameters);
        } else {
            throw new Exception("Invalid Signature Version specified");
        }
        
        return $this->_sign($stringToSign, $algorithm);
    }
    
    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private function _calculateStringToSignV2(array $parameters)
    {
        $data = 'POST';
        $data .= "\n";
        $data .= $this->_mwsEndpointUrl;
        $data .= "\n";
        $data .= $this->_mwsEndpointPath;
        $data .= "\n";
        $data .= $this->_getParametersAsString($parameters);
        return $data;
    }
    
    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }
        
        return implode('&', $queryParameters);
    }
    
    private function _urlencode($value)
    {
        return str_replace('%7E', '~', rawurlencode($value));
    }
    
    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception("Non-supported signing method specified");
        }
        
        return base64_encode(hash_hmac($hash, $data, $this->_config['secret_key'], true));
    }
    
    /**
     * Formats date as ISO 8601 timestamp
     */
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    /**
     * _invokePost takes the parameters and invokes the _httpPost function to POST the parameters
     * exponential retries on error 500 and 503
     * The response from the POST is an XML which is converted to Array
     */
    private function _invokePost($parameters)
    {
        $response   = array();
        $statusCode = 200;
	$this->_success = false;
        /* Submit the request and read response body */
        try {
            $shouldRetry = true;
            $retries     = 0;
            do {
                try {
                    $this->_constructUserAgentHeader();
                    $response   = $this->_httpPost($parameters);
                    $statusCode = $response['Status'];
                    
                    if ($statusCode == 200) {
                        $shouldRetry = false;
			$this->_success = true;
                    } elseif ($statusCode == 500 || $statusCode == 503) {
                        $shouldRetry = ($response['ErrorCode'] === 'RequestThrottled') ? false : true;
                        if ($shouldRetry && strtolower($this->_config['handle_throttle'])) {
                            $this->_pauseOnRetry(++$retries, $statusCode);
                        }
                    } else {
                        $shouldRetry = false;
                    }
                }
                
                catch (Exception $e) {
                    throw $e;
                }
            } while ($shouldRetry);
        }
        
        catch (Exception $se) {
            throw $se;
        }
        
        return $response;
    }
    
    /**
     * Perform HTTP post using Curl
     *
     */
    private function _httpPost($parameters)
    {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_mwsServiceUrl);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        // if a ca bundle is configured, use it as opposed to the default ca
        // configured for the server
        
        if (!is_null($this->_config['cabundle_file'])) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->_config['cabundle_file']);
        }
        
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
        $other = preg_split("/\r\n|\n|\r/", $other);
        
        list($protocol, $code, $text) = explode(' ', trim(array_shift($other)), 3);
        return array(
            'Status' => (int) $code,
            'ResponseBody' => $responseBody
        );
    }
    
    /**
     * Exponential sleep on failed request
     * @param retries current retry
     * @throws OffAmazonPaymentsService_Exception if maximum number of retries has been reached
     */
    private function _pauseOnRetry($retries, $status)
    {
        if ($retries <= self::MAX_ERROR_RETRY) {
            $delay = (int) (pow(4, $retries) * 100000);
            usleep($delay);
        } else {
            throw new Exception(array(
                'Message' => "Maximum number of retry attempts reached :  $retries",
                'StatusCode' => $status
            ));
        }
    }
    
    private function _createServiceUrl()
    {
        $this->_modePath = strtolower($this->_config['sandbox']) ? 'OffAmazonPayments_Sandbox' : 'OffAmazonPayments';
        
        $region = strtolower($this->_config['region']);
        if (array_key_exists($region, $this->_regionMappings)) {
            $this->_mwsEndpointUrl  = $this->_mwsServiceUrls[$this->_regionMappings[$region]];
            $this->_mwsServiceUrl   = 'https://' . $this->_mwsEndpointUrl . '/' . $this->_modePath . '/' . self::SERVICE_VERSION;
            $this->_mwsEndpointPath = '/' . $this->_modePath . '/' . self::SERVICE_VERSION;
        } else {
            throw new Exception($region . 'is not a supported region');
        }
    }
    
    private function _profileEndpointUrl()
    {
        $region = strtolower($this->_config['user_profile_region']);
        if ($this->_config['sandbox']) {
            if (array_key_exists($region, $this->_sandboxProfileEndpoint)) {
                $this->_profileEndpoint = $this->_sandboxProfileEndpoint[$region];
            }
        } elseif (array_key_exists($region, $this->_liveProfileEndpoint)) {
            $this->_profileEndpoint = $this->_liveProfileEndpoint[$region];
        }
    }
    
    private function _constructUserAgentHeader()
    {
        $this->_userAgent = $this->_quoteApplicationName($this->_config['application_name']) . '/' . $this->_quoteApplicationVersion($this->_config['application_version']);
        $this->_userAgent .= ' (';
        $this->_userAgent .= 'Language=PHP/' . phpversion();
        $this->_userAgent .= '; ';
        $this->_userAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
        $this->_userAgent .= '; ';
        $this->_userAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;
        $this->_userAgent .= ')';
    }
    
    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '/' characters from a string.
     * @param $s
     * @return string
     */
    private function _quoteApplicationName($s)
    {
        $quotedString = preg_replace('/ {2,}|\s/', ' ', $s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\//', '\\/', $quotedString);
        return $quotedString;
    }
    
    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '(' characters from a string.
     *
     * @param $s
     * @return string
     */
    private function _quoteApplicationVersion($s)
    {
        $quotedString = preg_replace('/ {2,}|\s/', ' ', $s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\(/', '\\(', $quotedString);
        return $quotedString;
    }
}