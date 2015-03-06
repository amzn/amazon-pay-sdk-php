
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

class OffAmazonPaymentsService_Client
{
    const MWS_CLIENT_VERSION = '2013-01-01';
    const SERVICE_VERSION = '2013-01-01';
    
    private $_userAgent = null;
    private $_mwsEndpointPath = null;
    private $_mwsEndpointUrl = null;
    private $_profileEndpoint = null;
    private $_config = array('seller_id' 	  => null,
			     'secret_key' 	  => null,
			     'access_key' 	  => null,
			     'mws_auth_token'  	  => null,
			     'service_url' 	  => null,
			     'region' 		  => 'na',
			     'currency_code'	  => 'USD',
			     'sandbox' 		  => false,
			     'platform_id'	  => null,
			     'cabundle_file' 	  => null,
			     'application_name'   => null,
			     'application_version'=> null,
			     'proxy_host' 	  => null,
			     'proxy_port' 	  => -1,
			     'proxy_username' 	  => null,
			     'proxy_password' 	  => null,
			     'client_id' 	  => null,
			     'user_profile_region'=> null,
			     'handle_throttle' 	  => true);
    private $_modePath = null;
    
    private $_mwsServiceUrl = array('eu' => 'mws-eu.amazonservices.com',
				    'na' => 'mws.amazonservices.com',
				    'jp' => 'mws.amazonservices.jp');
    
    private $_liveProfileEndpoint = array('uk' => 'https://api.amazon.co.uk',
					  'na' => 'https://api.amazon.com',
					  'us' => 'https://api.amazon.com',
					  'de' => 'https://api.amazon.de',
					  'jp' => 'https://api.amazon.co.jp');
    
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
    
    public function __construct($config = null)
    {
	if(is_array($config)){
	   $configArray = $config;
	}
	
	elseif((json_decode($config) != $config) && json_decode($config)){
	    $configArray = json_decode($config,true);
	}
	elseif((!is_array($config)) && file_exists($config)){
	  $jsonString = file_get_contents($config);
	  $configArray = json_decode($jsonString,true);
	}
	else{
	    throw new Exception ($config. ' is not a supported type or the JSON file is not found in the specified path'.PHP_EOL.
				 'Supported Input types are:'.PHP_EOL.
				 '1.'.$config. ' can be a JSON file name containing config data in JSON format'.PHP_EOL.
				 '2 '.$config. ' can be a PHP associative array'.PHP_EOL.
				 '3 '.$config. ' can be a JSON string'.PHP_EOL);
	}
	if(is_array($configArray)){
	$this->_checkConfigKeys($configArray);
	}else{
	  throw new Exception($configArray. ' is of the incorrect type '. gettype($configArray) .' and should be of the type array');  
	}
        
    }
    
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
        $this->_modePath = strtolower($this->_config['sandbox']) ? 'OffAmazonPayments_Sandbox' : 'OffAmazonPayments';
    }
    
    /* Setter
     * Sets the value for the key if the key exists in _config
     */
    public function __set($name, $value)
    {
        if (array_key_exists(strtolower($name), $this->_config)) {
            $this->_config[$name] = $value;
        } else {
            throw new Exception('Key ' . $name . ' is either not a part of the configuration array _config or the'
				. $name . 'does not match the key name in the _config array', 1);
        }
    }
    
    /* Getter
     * Gets the value for the key if the key exists in _config
     */
    public function __get($name)
    {
        if (array_key_exists(strtolower($name), $this->_config)) {
            return $this->_config[$name];
        } else {
            throw new Exception('Key ' . $name . ' is either not a part of the configuration array _config or the'
				. $name . 'does not match the key name in the _config array', 1);
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
        $userInfo = json_decode($response,true);
        return $userInfo;
    }
    
    /* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
     * 
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetOrderReferenceDetails.html
     * @param AmazonOrderReferenceId [String]
     * @optional AddressConsentToken [String] 
     */
    public function getOrderReferenceDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetOrderReferenceDetails';
        
        if (!empty($requestParameters['AmazonOrderReferenceId']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        if (!empty($requestParameters['AddressConsentToken']))
            $parameters['AddressConsentToken'] = $requestParameters['AddressConsentToken'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* SetOrderReferenceDetails API call - Sets order reference details such as the order total and a description for the order.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetOrderReferenceDetails.html
     *
     *  
     * @param AmazonOrderReferenceId [String]
     * @param Amount [String]
     * @param CurrencyCode [String]
     * @optional PlatformId [String]
     * @optional SellerNote [String]
     * @optional SellerOrderId [String]
     * @optional StoreName [String]
     * @optional CustomInformation [String]
     */
    public function setOrderReferenceDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetOrderReferenceDetails';
        
        
        if (!empty($requestParameters['AmazonOrderReferenceId'])) 
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        if (!empty($requestParameters['Amount']))
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $requestParameters['Amount'];
        
	    $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($this->_config['platform_id']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $this->_config['platform_id'];
        if (!empty($requestParameters['SellerNote']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $requestParameters['SellerNote'];
        if (!empty($requestParameters['SellerOrderId']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $requestParameters['SellerOrderId'];
        if (!empty($requestParameters['StoreName']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $requestParameters['StoreName'];
        if (!empty($requestParameters['CustomInformation']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $requestParameters['CustomInformation'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmOrderReference.html
     
     *  
     * @param AmazonOrderReferenceId [String]
     *  
     */
    public function confirmOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmOrderReference';
        
        
        if (!empty($requestParameters['AmazonOrderReferenceId'])) 
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CancelOrderReference.html
     *
     *  
     * @param AmazonOrderReferenceId [String]
     * @optional CancelationReason [String]
     *  
     */
    public function cancelOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CancelOrderReference';
        
        
        if (!empty($AmazonOrderReferenceId))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        if (!empty($requestParameters['CancelReason']))
            $parameters['CancelationReason'] = $requestParameters['CancelReason'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* CloseOrderReferenceDetails API call - Confirms that an order reference has been fulfilled (fully or partially)
     * and that you do not expect to create any new authorizations on this order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     *  
     * @param AmazonOrderReferenceId [String]
     * @optional ClosureReason [String]
     *  
     */
    public function closeOrderReference($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseOrderReference';
        
        
        
        if (!empty($requestParameters['AmazonOrderReferenceId']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        if (!empty($requestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $requestParameters['ClosureReason'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* CloseAuthorization API call - Closes an authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     * @param AmazonOrderReferenceId [String]
     * @optional ClosureReason [String]
     */
    public function closeAuthorization($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseAuthorization';
        
        
        
        if (!empty($requestParameters['AmazonAuthorizationId']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['AmazonAuthorizationId'];
        
        if (!empty($requestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $requestParameters['ClosureReason'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* Authorize API call - Reserves a specified amount against the payment method(s) stored in the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Authorize.html
     *
     *  
     * @param AmazonOrderReferenceId [String]
     * @param AuthorizeAmount [String]
     * @param CurrencyCode [String]
     * @optional AuthorizationReferenceId [String]
     * @optional CaptureNow [String]
     *  
     * @optional SellerAuthorizationNote [String]
     * @optional TransactionTimeout [String]
     * @optional SoftDescriptor [String]
     */
    public function authorize($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Authorize';
        
        
        
        if (!empty($requestParameters['AmazonOrderReferenceId']))
            $parameters['AmazonOrderReferenceId'] = $requestParameters['AmazonOrderReferenceId'];
        
        if (!empty($requestParameters['AuthorizeAmount']))
            $parameters['AuthorizationAmount.Amount'] = $requestParameters['AuthorizeAmount'];
        
	$parameters['AuthorizationAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        if (!empty($requestParameters['AuthorizationReferenceId'])){
            $parameters['AuthorizationReferenceId'] = $requestParameters['AuthorizationReferenceId'];
	} else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
        }
        
        if (!empty($requestParameters['CaptureNow']))
            $parameters['CaptureNow'] = strtolower($requestParameters['CaptureNow']);
        
        if (!empty($requestParameters['SellerAuthorizationNote']))
            $parameters['SellerAuthorizationNote'] = $requestParameters['SellerAuthorizationNote'];
        
	if (!empty($requestParameters['TransactionTimeout']))
            $parameters['TransactionTimeout'] = $requestParameters['TransactionTimeout'];
        
	if (!empty($requestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['SoftDescriptor'];
        
	$response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* Authorize API call - Returns the status of a particular authorization and the total amount captured on the authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetAuthorizationDetails.html
     *
     *  
     * @param AmazonAuthorizationId [String]
     *  
     */
    public function getAuthorizationDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetAuthorizationDetails';
        
        if (!empty($requestParameters['AmazonAuthorizationId']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['AmazonAuthorizationId'];
	    
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* Capture API call - Captures funds from an authorized payment instrument.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Capture.html
     *
     *  
     * @param AmazonAuthorizationId [String]
     * @param CaptureAmount [String]
     * @param CurrencyCode [String]
     * @optional CaptureReferenceId [String]
     *  
     * @optional SellerCaptureNote [String]
     * @optional SoftDescriptor [String]
     */
    public function capture($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Capture';
        
        if (!empty($requestParameters['AmazonAuthorizationId']))
            $parameters['AmazonAuthorizationId'] = $requestParameters['AmazonAuthorizationId'];
        
        if (!empty($requestParameters['CaptureAmount']))
            $parameters['CaptureAmount.Amount'] = $requestParameters['CaptureAmount'];
        
        $parameters['CaptureAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
	
        if (!empty($requestParameters['CaptureReferenceId'])) {
            $parameters['CaptureReferenceId'] = $requestParameters['CaptureReferenceId'];
        } else {
            $parameters['CaptureReferenceId'] = uniqid('C01_REF_');
        }
        
        if (!empty($requestParameters['SellerCaptureNote']))
            $parameters['SellerCaptureNote'] = $requestParameters['SellerCaptureNote'];
        if (!empty($requestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['SoftDescriptor'];
        
	$response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetCaptureDetails.html
     *
     *  
     * @param AmazonCaptureId [String]
     *  
     */
    
    public function getCaptureDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetCaptureDetails';
        
        if (!empty($requestParameters['AmazonCaptureId']))
            $parameters['AmazonCaptureId'] = $requestParameters['AmazonCaptureId'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* Refund API call - Refunds a previously captured amount.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Refund.html
     *
     *  
     * @param AmazonCaptureId [String]
     * @param RefundReferenceId [String]
     * @param RefundAmount [String]
     * @param CurrencyCode [String]
     *  
     * @optional SellerRefundNote [String]
     * @optional SoftDescriptor [String]
     */
    public function refund($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Refund';
        
        if (!empty($requestParameters['AmazonCaptureId']))
            $parameters['AmazonCaptureId'] = $requestParameters['AmazonCaptureId'];
        
        if (!empty($requestParameters['RefundReferenceId'])) {
            $parameters['RefundReferenceId'] = $requestParameters['RefundReferenceId'];
        } else {
            $parameters['RefundReferenceId'] = uniqid('R01_REF_');
        }
        
        if (!empty($requestParameters['RefundAmount']))
            $parameters['RefundAmount.Amount'] = $requestParameters['RefundAmount'];
        
	$parameters['RefundAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
	
        if (!empty($requestParameters['SellerRefundNote']))
            $parameters['SellerRefundNote'] = $requestParameters['SellerRefundNote'];
        if (!empty($requestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['SoftDescriptor'];
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* GetRefundDetails API call - Returns the status of a particular refund.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetRefundDetails.html
     *
     *  
     * @param AmazonRefundId [String]
     *  
     */
    
    public function getRefundDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetRefundDetails';
        
        if (!empty($requestParameters['AmazonRefundId']))
            $parameters['AmazonRefundId'] = $requestParameters['AmazonRefundId'];
        
	$response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* GetServiceStatus API Call - Returns the operational status of the Off-Amazon Payments API section
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetServiceStatus.html
     *
     *The GetServiceStatus operation returns the operational status of the Off-Amazon Payments API
     *section of Amazon Marketplace Web Service (Amazon MWS).
     *Status values are GREEN, GREEN_I, YELLOW, and RED.
     */
    
    public function getServiceStatus($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetServiceStatus';
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* CreateOrderReferenceForId API Call - Creates an order reference for the given object
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CreateOrderReferenceForId.html
     *
     * @param Id [String]
     * @optional InheritShippingAddress [Boolean]
     * @optional ConfirmNow [Boolean]
     * @optional Amount [Float] (required when confirm_now is set to true)
     * @optional CurrencyCode [String]
     * @optional SellerNote [String]
     * @optional SellerOrderId [String]
     * @optional StoreName [String]
     * @optional CustomInformation [String]
     */
    
    public function createOrderReferenceForId($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CreateOrderReferenceForId';
        
        if (!empty($requestParameters['Id']))
            $parameters['Id'] = $requestParameters['Id'];
        
        if (!empty($requestParameters['InheritShippingAddress'])){
            $parameters['InheritShippingAddress'] = strtolower($requestParameters['InheritShippingAddress']);
        } else {
	    $parameters['InheritShippingAddress'] = true;
	}
        if (!empty($requestParameters['ConfirmNow'])){
            $parameters['ConfirmNow'] = strtolower($requestParameters['ConfirmNow']);
        } else{
	    $parameters['ConfirmNow'] = false;
	}
        if (!empty($requestParameters['Amount']))
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $requestParameters['Amount'];
	    
	    $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = strtoupper($this->_config['currency_code']);
	    
        if (!empty($this->_config['platform_id']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $this->_config['platform_id'];
        if (!empty($requestParameters['SellerNote']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $requestParameters['SellerNote'];
        if (!empty($requestParameters['SellerOrderId']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $requestParameters['SellerOrderId'];
        if (!empty($requestParameters['StoreName']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $requestParameters['StoreName'];
        if (!empty($requestParameters['CustomInformation']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $requestParameters['CustomInformation'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetBillingAgreementDetails.html
     * @param AmazonBillingAgreementId [String] 
     */
    
    public function getBillingAgreementDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetBillingAgreementDetails';
        
        if (!empty($requestParameters['AmazonBillingAgreementId']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        
        if (!empty($requestParameters['AddressConsentToken']))
            $parameters['AddressConsentToken'] = $requestParameters['AddressConsentToken'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* SetBillingAgreementDetails API call - Sets billing agreement details such as a description of the agreement and other information about the seller.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetBillingAgreementDetails.html
     *
     *  
     * @param AmazonBillingAgreementId [String]
     * @param Amount [String]
     * @param CurrencyCode [String]
     * @optional PlatformId [String]
     * @optional SellerNote [String]
     * @optional SellerBillingAgreementId [String]
     * @optional StoreName [String]
     * @optional CustomInformation [String]
     */
    
    public function setBillingAgreementDetails($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetBillingAgreementDetails';
        
        if (!empty($requestParameters['AmazonBillingAgreementId']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        
        if (!empty($this->_config['platform_id']))
            $parameters['BillingAgreementAttributes.PlatformId'] = $this->_config['platform_id'];
        if (!empty($requestParameters['SellerNote']))
            $parameters['BillingAgreementAttributes.SellerNote'] = $requestParameters['SellerNote'];
        if (!empty($requestParameters['SellerBillingAgreementId']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId'] = $requestParameters['SellerBillingAgreementId'];
        if (!empty($requestParameters['CustomInformation']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation'] = $requestParameters['CustomInformation'];
        if (!empty($requestParameters['StoreName']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName'] = $requestParameters['StoreName'];
        
	$response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* ConfirmBillingAgreement API Call - Confirms that the billing agreement is free of constraints and all required information has been set on the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmBillingAgreement.html
     * @param AmazonBillingAgreementId [String] 
     */
    public function confirmBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmBillingAgreement';
        
        if (!empty($requestParameters['AmazonBillingAgreementId']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* ValidateBillignAgreement API Call - Validates the status of the BillingAgreement object and the payment method associated with it.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ValidateBillignAgreement.html
     *
     *  
     * @param AmazonBillingAgreementId [String]
     *  
     */
    public function validateBillignAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ValidateBillingAgreement';
        
        if (!empty($requestParameters['AmazonBillingAgreementId']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* AuthorizeOnBillingAgreement API call - Reserves a specified amount against the payment method(s) stored in the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_AuthorizeOnBillingAgreement.html
     *
     *  
     * @param AmazonBillingAgreementId [String]
     * @param AuthorizationReferenceId [String]
     * @param AuthorizationAmount [String]
     * @param CurrencyCode [String]
     * @optional SellerAuthorizationNote [String]
     * @optional TransactionTimeout - Defaults to 0 -Synchronous
     * @optional CaptureNow [String]
     * @optional SoftDescriptor [String]
     * @optional SellerNote [String]
     * @optional PlatformId [String]
     * @optional CustomInformation [String]
     * @optional SellerOrderId [String]
     * @optional StoreName [String]
     * @optional InheritShippingAddress [Boolean] - Defaults to true
     *  
     */
    public function authorizeOnBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'AuthorizeOnBillingAgreement';
        
        if (!empty($requestParameters['AmazonBillingAgreementId']))
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        
        if (!empty($requestParameters['AuthorizationReferenceId'])) {
            $parameters['AuthorizationReferenceId'] = $requestParameters['AuthorizationReferenceId'];
        } else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
        }
        
        if (!empty($requestParameters['AuthorizationAmount']))
            $parameters['AuthorizationAmount.Amount'] = $requestParameters['AuthorizationAmount'];
        
	    $parameters['AuthorizationAmount.CurrencyCode'] = strtoupper($this->_config['currency_code']);
        
        
        if (!empty($requestParameters['SellerAuthorizationNote']))
            $parameters['SellerAuthorizationNote'] = $requestParameters['SellerAuthorizationNote'];
        if (!empty($requestParameters['TransactionTimeout']))
            $parameters['TransactionTimeout'] = $requestParameters['TransactionTimeout'];
        if (!empty($requestParameters['CaptureNow']))
            $parameters['CaptureNow'] = strtolower($requestParameters['CaptureNow']);
        if (!empty($requestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $requestParameters['SoftDescriptor'];
        if (!empty($requestParameters['SellerNote']))
            $parameters['SellerNote'] = $requestParameters['SellerNote'];
        if (!empty($this->_config['platform_id']))
            $parameters['PlatformId'] = $this->_config['platform_id'];
        if (!empty($requestParameters['CustomInformation']))
            $parameters['SellerOrderAttributes.CustomInformation'] = $requestParameters['CustomInformation'];
        if (!empty($requestParameters['SellerOrderId']))
            $parameters['SellerOrderAttributes.SellerOrderId'] = $requestParameters['SellerOrderId'];
        if (!empty($requestParameters['StoreName']))
            $parameters['SellerOrderAttributes.StoreName'] = $requestParameters['StoreName'];
        if (!empty($requestParameters['InheritShippingAddress'])) {
            $parameters['InheritShippingAddress'] = strtolower($requestParameters['InheritShippingAddress']);
        } else {
            $parameters['InheritShippingAddress'] = true;
        }
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseBillingAgreement.html
     *
     *  
     * @param AmazonBillingAgreementId [String]
     * @optional ClosureReason [String]
     *  
     */
    public function CloseBillingAgreement($requestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseBillingAgreement';
        
        if (!empty($requestParameters['AmazonBillingAgreementId'])) 
            $parameters['AmazonBillingAgreementId'] = $requestParameters['AmazonBillingAgreementId'];
        if (!empty($requestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $requestParameters['ClosureReason'];
        
        
        $response = $this->_calculateSignatureAndPost($parameters);
        return ($response);
    }
    
    /*Create an Array of required parameters, sort them
     *calculate signature and invoke the POST them to the MWS Service URL
     * @param AWSAccessKeyId [String]
     * @param Version [String]
     * @param SignatureMethod [String]
     * @param Timestamp [String]
     * @param Signature [String]
     */
    private function _calculateSignatureAndPost($parameters)
    {
        if (!empty($this->_config['mws_auth_token'])) {
            $parameters['MWSAuthToken'] = $this->_config['mws_auth_token'];
        }
        if (!empty($this->_config['seller_id'])) {
            $parameters['SellerId'] = $this->_config['seller_id'];
        }
        
        $parameters['AWSAccessKeyId']   = $this->_config['access_key'];
        $parameters['Version']          = self::SERVICE_VERSION;
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->_getFormattedTimestamp();
        uksort($parameters, 'strcmp');
        
        $this->_createServiceUrl();
        
        $parameters['Signature'] = $this->_signParameters($parameters);
        $parameters              = $this->_getParametersAsString($parameters);
        $response         	 = $this->_invokePost($parameters);
        return $response;
    }
    
    /* toJson  - converts XML into Json
     * @param $response [XML]
     */
    public function toJson($response)
    {
	//Getting the HttpResponse Status code to the output as a string
	$status = strval($response['Status']);
	
	//Getting the Simple XML element object of the XML Response Body
	$response = simplexml_load_string((string)$response['ResponseBody']);
	
	//Adding the HttpResponse Status code to the output as a string
	$response->addChild('ResponseStatus', $status);
	
	return(json_encode($response));
    }
    
    /* toArray  - converts XML into associative array
     * @param $response [XML]
     */
    public function toArray($response)
    {
	//Getting the HttpResponse Status code to the output as a string
	$status = strval($response['Status']);
	
	//Getting the Simple XML element object of the XML Response Body
	$response = simplexml_load_string((string)$response['ResponseBody']);
	
	//Adding the HttpResponse Status code to the output as a string
	$response->addChild('ResponseStatus', $status);
	
	//Converting the SimpleXMLElement Object to array()
	$response = json_encode($response);
	
	return(json_decode($response,true));
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
        $response     = array();
        $statusCode   = 200;
        /* Submit the request and read response body */
        try {
            $shouldRetry = true;
            $retries     = 0;
            do {
                try {
		    $this->_constructUserAgentHeader();
                    $response     = $this->_httpPost($parameters);
                    $statusCode   = $response['Status'];
                    
                    if ($statusCode == 200) {
                        $shouldRetry = false;
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
        curl_setopt($ch, CURLOPT_URL, $this->_config['service_url']);
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
        $region = strtolower($this->_config['region']);
        if (array_key_exists($region, $this->_regionMappings)) {
	    $this->_mwsEndpointUrl = $this->_mwsServiceUrl[$this->_regionMappings[$region]];
            $this->_config['service_url'] = 'https://'.$this->_mwsEndpointUrl . '/' . $this->_modePath . '/' . self::SERVICE_VERSION;
            $this->_mwsEndpointPath         = '/' . $this->_modePath . '/' . self::SERVICE_VERSION;
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
        } else {
            if (array_key_exists($region, $this->_liveProfileEndpoint)) {
                $this->_profileEndpoint = $this->_liveProfileEndpoint[$region];
            }
        }
    }
    
    private function _constructUserAgentHeader()
    {
        $this->_userAgent = $this->_quoteApplicationName($this->application_name) . '/' . $this->_quoteApplicationVersion($this->application_version);
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