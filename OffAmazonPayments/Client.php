
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
    const SERVICE_VERSION = '2013-01-01';
    const SANDBOX = 'SANDBOX';
    const LIVE = 'LIVE';
    const SANDBOX_PATH = 'OffAmazonPayments_Sandbox';
    const LIVE_PATH = 'OffAmazonPayments';
    private $_UserAgent = null;
    private $_endpointpath = null;
    private $ProfileEndpoint = null;
    private $_Config = array(
		'SellerId'           => null,
		'secretKey'          => null,
		'accessKey'          => null,
		'serviceUrl'         => null,
		'region'             => null,
		'environment'        => null,
		'caBundleFile'       => null,
		'applicationName'    => null,
		'applicationVersion' => null,
		'ProxyHost'          => null,
		'ProxyPort'          => - 1,
		'ProxyUsername'      => null,
		'ProxyPassword'      => null,
		'clientId'           => null,
		'UserProfileRegion'  => null
	);
    private $MPSserviceUrls = array('eu' => 'https://mws-eu.amazonservices.com',
                                    'na' => 'https://mws.amazonservices.com');
    
    private $LiveProfileEndpoint = array('uk' => 'https://api.amazon.co.uk',
					 'na' => 'https://api.amazon.com',
					 'us' => 'https://api.amazon.com',
					 'de' => 'https://api.amazon.de');
    
    private $SandboxProfileEndpoint = array('uk' => 'https://api.sandbox.amazon.co.uk',
					    'na' => 'https://api.sandbox.amazon.com',
					    'us' => 'https://api.sandbox.amazon.com',
					    'de' => 'https://api.sandbox.amazon.de');
    
    private $MPSregionMappings = array('de' => 'eu',
                                       'na' => 'na',
                                       'uk' => 'eu',
                                       'us' => 'na');
    
    public function __construct($config = null)
    {
        $this->checkConfigHasAllRequiredKeys($config);
       
    }
    
    private function checkConfigHasAllRequiredKeys($config)
    {
	
	foreach ($config as $key => $value) {
	    if (array_key_exists($key, $this->_Config)) {
            $this->_Config[$key] = $value;
	    } else {
            throw new Exception('Key ' . $name . ' is either not part of the configuration or has incorrect Key name.
				check the _Config array key names to match your key names of your config array ', 1);
	    }
	}
	
        if ($this->_Config['SellerId'] == '') {
            throw new InvalidArgumentException('merchantId is a required parameter and is not set');
        }
        
        if ($this->_Config['accessKey'] == '') {
            throw new InvalidArgumentException('accessKey is a required parameter and is not set');
        }
        
        if ($this->_Config['secretKey'] == '') {
            throw new InvalidArgumentException('secretKey is a required parameter and is not set');
        }
        
        if ($this->_Config['region'] == '') {
            throw new InvalidArgumentException('region is a required parameter and is not set');
        }
        
        if ($this->_Config['environment'] == '') {
            throw new InvalidArgumentException('environment is a required parameter and is not set');
        }
    }
    
    /* Setter
     * Sets the value for the key if the key exists in _Config
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_Config)) {
            $this->_Config[$name] = $value;
        } else {
            throw new Exception('Key ' . $name . ' is either not a part of the configuration array _Config or the' . $name .
				'does not match the key name in the _Config array', 1);
        }
    }
    
    /* Setter
     * Gets the value for the key if the key exists in _Config
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_Config)) {
            return $this->_Config[$name];
        } else {
            throw new Exception('Key ' . $name . ' is either not a part of the configuration array _Config or the' . $name .
				'does not match the key name in the _Config array', 1);
        }
    }
    
    /* GetUserInfo convenience funtion - Returns user's profile information from Amazon using the access token returned by the Button widget.
     * 
     * @see http://docs.developer.amazonservices.com/en_US/apa_guide/APAGuide_ObtainProfile.html
     * @param $access_token [String]
     * @param _Config['UserProfileRegion'] [String]
     */
    public function GetUserInfo($access_token)
    {
	//Get the correct Profile Endpoint URL based off the country/region provided in the _Config['UserProfileRegion']
	if(!empty($this->_Config['UserProfileRegion']))
	{
	    $this->ProfileEndpointUrl();
	}
	else{
	    throw new InvalidArgumentException('Profile Region is a required parameter and is not set.');
	}
	if(empty($access_token))
	{
	    throw new InvalidArgumentException('Access Token is a required parameter and is not set');
	}
	//to make sure double encoding doesn't occur decode first and encode again.
	$access_token = urldecode($access_token);
	
	$c = curl_init($this->ProfileEndpoint.'/auth/o2/tokeninfo?access_token='. urlencode($access_token));
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	 if (!$response = curl_exec($c)) {
            $error_msg = 'Unable to post request, underlying exception of ' . curl_error($c);
            curl_close($c);
            throw new Exception($error_msg);
        }
	curl_close($c);
	$data = json_decode($response);
	
	if ($data->aud != $this->_Config['clientId']) {
	// the access token does not belong to us
	header('HTTP/1.1 404 Not Found');
	throw new Exception('The Access token entered is incorrect');
	}

	// exchange the access token for user profile
	$c = curl_init($this->ProfileEndpoint.'/user/profile');
	curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer '. $access_token));
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	if (!$response = curl_exec($c)) {
            $error_msg = 'Unable to post request, underlying exception of ' . curl_error($c);
            curl_close($c);
            throw new Exception($error_msg);
        }
	curl_close($c);
	$UserInfoObject = json_decode($response);
	return $UserInfoObject;
    }
    
    /* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
     * 
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetOrderReferenceDetails.html
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @optional AddressConsentToken [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function GetOrderReferenceDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetOrderReferenceDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonOrderReferenceId'])) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['AddressConsentToken']))
            $parameters['AddressConsentToken'] = $RequestParameters['AddressConsentToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* SetOrderReferenceDetails API call - Sets order reference details such as the order total and a description for the order.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetOrderReferenceDetails.html
     *
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @param Amount [String]
     * @param CurrencyCode [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     * @optional PlatformId [String]
     * @optional SellerNote [String]
     * @optional SellerOrderId [String]
     * @optional StoreName [String]
     * @optional CustomInformation [String]
     */
    public function SetOrderReferenceDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetOrderReferenceDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonOrderReferenceId'])) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['Amount'])) {
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $RequestParameters['Amount'];
        } else {
            throw new InvalidArgumentException("Amount is a required parameter");
        }
        
        if (!empty($RequestParameters['CurrencyCode'])) {
            $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        } else {
            throw new InvalidArgumentException("CurrencyCode is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['PlatformId']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $RequestParameters['PlatformId'];
        if (!empty($RequestParameters['SellerNote']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $RequestParameters['SellerNote'];
        if (!empty($RequestParameters['SellerOrderId']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $RequestParameters['SellerOrderId'];
        if (!empty($RequestParameters['StoreName']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $RequestParameters['StoreName'];
        if (!empty($RequestParameters['CustomInformation']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $RequestParameters['CustomInformation'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmOrderReference.html
     
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function ConfirmOrderReference($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmOrderReference';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonOrderReferenceId'])) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CancelOrderReference.html
     *
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @optional CancelationReason [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function CancelOrderReference($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CancelOrderReference';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($AmazonOrderReferenceId)) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['CancelReason']))
            $parameters['CancelationReason'] = $RequestParameters['CancelReason'];
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* CloseOrderReferenceDetails API call - Confirms that an order reference has been fulfilled (fully or partially)
     * and that you do not expect to create any new authorizations on this order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @optional ClosureReason [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function CloseOrderReference($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseOrderReference';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonOrderReferenceId'])) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $RequestParameters['ClosureReason'];
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* CloseAuthorization API call - Closes an authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @optional AddressConsentToken [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function CloseAuthorization($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseAuthorization';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonAuthorizationId'])) {
            $parameters['AmazonAuthorizationId'] = $RequestParameters['AmazonAuthorizationId'];
        } else {
            throw new InvalidArgumentException("AuthorizationId is a required parameter");
        }
        
        if (!empty($RequestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $RequestParameters['ClosureReason'];
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* Authorize API call - Reserves a specified amount against the payment method(s) stored in the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Authorize.html
     *
     * @param SellerId [String]
     * @param AmazonOrderReferenceId [String]
     * @param AuthorizeAmount [String]
     * @param CurrencyCode [String]
     * @optional AuthorizationReferenceId [String]
     * @optional CaptureNow [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     * @optional SellerAuthorizationNote [String]
     * @optional TransactionTimeout [String]
     * @optional SoftDescriptor [String]
     */
    public function Authorize($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Authorize';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonOrderReferenceId'])) {
            $parameters['AmazonOrderReferenceId'] = $RequestParameters['AmazonOrderReferenceId'];
        } else {
            throw new InvalidArgumentException("AmazonOrderReferenceId is a required parameter");
        }
        
        if (!empty($RequestParameters['AuthorizeAmount'])) {
            $parameters['AuthorizationAmount.Amount'] = $RequestParameters['AuthorizeAmount'];
        } else {
            throw new InvalidArgumentException("AuthorizeAmount variable is a required parameter and is not set");
        }
        
        if (!empty($RequestParameters['CurrencyCode'])) {
            $parameters['AuthorizationAmount.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        } else {
            throw new InvalidArgumentException("CurrencyCode is a required parameter");
        }
        
        if (!empty($RequestParameters['AuthorizationReferenceId'])) {
            $parameters['AuthorizationReferenceId'] = $RequestParameters['AuthorizationReferenceId'];
        } else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
        }
        
        if (!empty($RequestParameters['CaptureNow'])) {
            $parameters['CaptureNow'] = strtolower($RequestParameters['CaptureNow']);
        } else {
            $parameters['CaptureNow'] = 'false';
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['SellerAuthorizationNote']))
            $parameters['SellerAuthorizationNote'] = $RequestParameters['SellerAuthorizationNote'];
        if (!empty($RequestParameters['TransactionTimeout']))
            $parameters['TransactionTimeout'] = $RequestParameters['TransactionTimeout'];
        if (!empty($RequestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $RequestParameters['SoftDescriptor'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
     /* Authorize API call - Returns the status of a particular authorization and the total amount captured on the authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetAuthorizationDetails.html
     *
     * @param SellerId [String]
     * @param AmazonAuthorizationId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function GetAuthorizationDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetAuthorizationDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonAuthorizationId'])) {
            $parameters['AmazonAuthorizationId'] = $RequestParameters['AmazonAuthorizationId'];
        } else {
            throw new InvalidArgumentException("AuthorizationId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* Capture API call - Captures funds from an authorized payment instrument.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Capture.html
     *
     * @param SellerId [String]
     * @param AmazonAuthorizationId [String]
     * @param CaptureAmount [String]
     * @param CurrencyCode [String]
     * @optional CaptureReferenceId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     * @optional SellerCaptureNote [String]
     * @optional SoftDescriptor [String]
     */
    public function Capture($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Capture';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonAuthorizationId'])) {
            $parameters['AmazonAuthorizationId'] = $RequestParameters['AmazonAuthorizationId'];
        } else {
            throw new InvalidArgumentException("AuthorizationId is a required parameter");
        }
        
        if (!empty($RequestParameters['CaptureAmount'])) {
            $parameters['CaptureAmount.Amount'] = $RequestParameters['CaptureAmount'];
        } else {
            throw new InvalidArgumentException("CaptureAmount is a required parameter");
        }
        
        if (!empty($RequestParameters['CurrencyCode'])) {
            $parameters['CaptureAmount.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        } else {
            throw new InvalidArgumentException("CurrencyCode is a required parameter");
        }
        
        if (!empty($RequestParameters['CaptureReferenceId'])) {
            $parameters['CaptureReferenceId'] = $RequestParameters['CaptureReferenceId'];
        } else {
            $parameters['CaptureReferenceId'] = uniqid('C01_REF_');
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['SellerCaptureNote']))
            $parameters['SellerCaptureNote'] = $RequestParameters['SellerCaptureNote'];
        if (!empty($RequestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $RequestParameters['SoftDescriptor'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetCaptureDetails.html
     *
     * @param SellerId [String]
     * @param AmazonCaptureId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    
    public function GetCaptureDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetCaptureDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonCaptureId'])) {
            $parameters['AmazonCaptureId'] = $RequestParameters['AmazonCaptureId'];
        } else {
            throw new InvalidArgumentException("CaptureId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* Refund API call - Refunds a previously captured amount.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Refund.html
     *
     * @param SellerId [String]
     * @param AmazonCaptureId [String]
     * @param RefundReferenceId [String]
     * @param RefundAmount [String]
     * @param CurrencyCode [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     * @optional SellerRefundNote [String]
     * @optional SoftDescriptor [String]
     */
    public function Refund($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'Refund';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonCaptureId'])) {
            $parameters['AmazonCaptureId'] = $RequestParameters['AmazonCaptureId'];
        } else {
            throw new InvalidArgumentException("CaptureId is a required parameter");
        }
        
        if (!empty($RequestParameters['RefundReferenceId'])) {
            $parameters['RefundReferenceId'] = $RequestParameters['RefundReferenceId'];
        } else {
            $parameters['RefundReferenceId'] = uniqid('R01_REF_');
        }
        
        if (!empty($RequestParameters['RefundAmount'])) {
            $parameters['RefundAmount.Amount'] = $RequestParameters['RefundAmount'];
        } else {
            throw new InvalidArgumentException("RefundAmount is a required parameter");
        }
        
        if (!empty($RequestParameters['CurrencyCode'])) {
            $parameters['RefundAmount.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        } else {
            throw new InvalidArgumentException("CurrencyCode is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['SellerRefundNote']))
            $parameters['SellerRefundNote'] = $RequestParameters['SellerRefundNote'];
        if (!empty($RequestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $RequestParameters['SoftDescriptor'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* GetRefundDetails API call - Returns the status of a particular refund.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetRefundDetails.html
     *
     * @param SellerId [String]
     * @param AmazonRefundId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    
    public function GetRefundDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetRefundDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonRefundId'])) {
            $parameters['AmazonRefundId'] = $RequestParameters['AmazonRefundId'];
        } else {
            throw new InvalidArgumentException("RefundId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* GetServiceStatus API Call - Returns the operational status of the Off-Amazon Payments API section
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetServiceStatus.html
     *
     *The GetServiceStatus operation returns the operational status of the Off-Amazon Payments API
     *section of Amazon Marketplace Web Service (Amazon MWS).
     *Status values are GREEN, GREEN_I, YELLOW, and RED.
     */
    
    public function GetServiceStatus($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetServiceStatus';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
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
    
    public function CreateOrderReferenceForId($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CreateOrderReferenceForId';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['Id'])) {
            $parameters['Id'] = $RequestParameters['Id'];
        } else {
            throw new InvalidArgumentException("Id is a required parameter");
        }
        
        if (!empty($RequestParameters['InheritShippingAddress']))
            $parameters['InheritShippingAddress'] = strtolower($RequestParameters['InheritShippingAddress']);
        if (!empty($RequestParameters['ConfirmNow']))
            $parameters['ConfirmNow'] = strtolower($RequestParameters['ConfirmNow']);
        if (!empty($RequestParameters['Amount']))
            $parameters['OrderReferenceAttributes.OrderTotal.Amount'] = $RequestParameters['Amount'];
        if (!empty($RequestParameters['CurrencyCode']))
            $parameters['OrderReferenceAttributes.OrderTotal.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        if (!empty($RequestParameters['PlatformId']))
            $parameters['OrderReferenceAttributes.PlatformId'] = $RequestParameters['PlatformId'];
        if (!empty($RequestParameters['SellerNote']))
            $parameters['OrderReferenceAttributes.SellerNote'] = $RequestParameters['SellerNote'];
        if (!empty($RequestParameters['SellerOrderId']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId'] = $RequestParameters['SellerOrderId'];
        if (!empty($RequestParameters['StoreName']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.StoreName'] = $RequestParameters['StoreName'];
        if (!empty($RequestParameters['CustomInformation']))
            $parameters['OrderReferenceAttributes.SellerOrderAttributes.CustomInformation'] = $RequestParameters['CustomInformation'];
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
     /* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetBillingAgreementDetails.html
     *
     * @param SellerId [String]
     * @param AmazonBillingAgreementId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
     
    public function GetBillingAgreementDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'GetBillingAgreementDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* SetBillingAgreementDetails API call - Sets billing agreement details such as a description of the agreement and other information about the seller.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetBillingAgreementDetails.html
     *
     * @param SellerId [String]
     * @param AmazonBillingAgreementId [String]
     * @param Amount [String]
     * @param CurrencyCode [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     * @optional PlatformId [String]
     * @optional SellerNote [String]
     * @optional SellerBillingAgreementId [String]
     * @optional StoreName [String]
     * @optional CustomInformation [String]
     */
    
    public function SetBillingAgreementDetails($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'SetBillingAgreementDetails';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        if (!empty($RequestParameters['PlatformId']))
            $parameters['BillingAgreementAttributes.PlatformId'] = $RequestParameters['PlatformId'];
        if (!empty($RequestParameters['SellerNote']))
            $parameters['BillingAgreementAttributes.SellerNote'] = $RequestParameters['SellerNote'];
        if (!empty($RequestParameters['SellerBillingAgreementId']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId'] = $RequestParameters['SellerBillingAgreementId'];
        if (!empty($RequestParameters['CustomInformation']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation'] = $RequestParameters['CustomInformation'];
        if (!empty($RequestParameters['StoreName']))
            $parameters['BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName'] = $RequestParameters['StoreName'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* ConfirmBillingAgreement API Call - Confirms that the billing agreement is free of constraints and all required information has been set on the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmBillingAgreement.html
     *
     * @param SellerId [String]
     * @param AmazonBillingAgreementId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function ConfirmBillingAgreement($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ConfirmBillingAgreement';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* ValidateBillignAgreement API Call - Validates the status of the BillingAgreement object and the payment method associated with it.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ValidateBillignAgreement.html
     *
     * @param SellerId [String]
     * @param AmazonBillingAgreementId [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function ValidateBillignAgreement($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'ValidateBillingAgreement';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* AuthorizeOnBillingAgreement API call - Reserves a specified amount against the payment method(s) stored in the billing agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_AuthorizeOnBillingAgreement.html
     *
     * @param SellerId [String]
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
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function AuthorizeOnBillingAgreement($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'AuthorizeOnBillingAgreement';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['AuthorizationReferenceId'])) {
            $parameters['AuthorizationReferenceId'] = $RequestParameters['AuthorizationReferenceId'];
        } else {
            $parameters['AuthorizationReferenceId'] = uniqid('A01_REF_');
            ;
        }
        
        if (!empty($RequestParameters['AuthorizationAmount'])) {
            $parameters['AuthorizationAmount.Amount'] = $RequestParameters['AuthorizationAmount'];
        } else {
            throw new InvalidArgumentException("AuthorizationAmount is a required parameter");
        }
        
        if (!empty($RequestParameters['CurrencyCode'])) {
            $parameters['AuthorizationAmount.CurrencyCode'] = $RequestParameters['CurrencyCode'];
        } else {
            throw new InvalidArgumentException("CurrencyCode is a required parameter");
        }
        
        if (!empty($RequestParameters['SellerAuthorizationNote']))
            $parameters['SellerAuthorizationNote'] = $RequestParameters['SellerAuthorizationNote'];
        if (!empty($RequestParameters['TransactionTimeout']))
            $parameters['TransactionTimeout'] = $RequestParameters['TransactionTimeout'];
        if (!empty($RequestParameters['CaptureNow']))
            $parameters['CaptureNow'] = $RequestParameters['CaptureNow'];
        if (!empty($RequestParameters['SoftDescriptor']))
            $parameters['SoftDescriptor'] = $RequestParameters['SoftDescriptor'];
        if (!empty($RequestParameters['SellerNote']))
            $parameters['SellerNote'] = $RequestParameters['SellerNote'];
        if (!empty($RequestParameters['PlatformId']))
            $parameters['PlatformId'] = $RequestParameters['PlatformId'];
        if (!empty($RequestParameters['CustomInformation']))
            $parameters['SellerOrderAttributes.CustomInformation'] = $RequestParameters['CustomInformation'];
        if (!empty($RequestParameters['SellerOrderId']))
            $parameters['SellerOrderAttributes.SellerOrderId'] = $RequestParameters['SellerOrderId'];
        if (!empty($RequestParameters['StoreName']))
            $parameters['SellerOrderAttributes.StoreName'] = $RequestParameters['StoreName'];
        if (!empty($RequestParameters['InheritShippingAddress'])) {
            $parameters['InheritShippingAddress'] = $RequestParameters['InheritShippingAddress'];
        } else {
            $parameters['InheritShippingAddress'] = true;
        }
        
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseBillingAgreement.html
     *
     * @param SellerId [String]
     * @param AmazonBillingAgreementId [String]
     * @optional ClosureReason [String]
     * @optional MWSAuthToken [String] (required only for Solution Porviders and Marketplace owners)
     */
    public function CloseBillingAgreement($RequestParameters = null)
    {
        $parameters           = array();
        $parameters['Action'] = 'CloseBillingAgreement';
        if (!empty($RequestParameters['SellerId'])) {
            $parameters['SellerId'] = $RequestParameters['SellerId'];
        } else {
            $parameters['SellerId'] = $this->_Config['SellerId'];
        }
        
        if (!empty($RequestParameters['AmazonBillingAgreementId'])) {
            $parameters['AmazonBillingAgreementId'] = $RequestParameters['AmazonBillingAgreementId'];
        } else {
            throw new InvalidArgumentException("AmazonBillingAgreementId is a required parameter");
        }
        
        if (!empty($RequestParameters['ClosureReason']))
            $parameters['ClosureReason'] = $RequestParameters['ClosureReason'];
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
    /*Create an Array of required parameters, sort them
     *calculate signature and invoke the POST them to the MWS Service URL
     * @param AWSAccessKeyId [String]
     * @param Version [String]
     * @param SignatureMethod [String]
     * @param Timestamp [String]
     * @param Signature [String]
     */
    private function CalculatesignatureAndPost($parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_Config['accessKey'];
        $parameters['Version']          = self::SERVICE_VERSION;
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->GetFormattedTimestamp();
        uksort($parameters, 'strcmp');
	$this->createServiceUrl();
        $parameters['Signature'] = $this->SignParameters($parameters);
        $parameters              = $this->GetParametersAsString($parameters);
        $ResponseToArray         = $this->InvokePost($parameters);
        return $ResponseToArray;
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
    private function SignParameters(array $parameters)
    {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm        = "HmacSHA1";
        $stringToSign     = null;
        if (2 === $signatureVersion) {
            $algorithm                     = "HmacSHA256";
            $parameters['SignatureMethod'] = $algorithm;
            $stringToSign                  = $this->CalculateStringToSignV2($parameters);
        } else {
            throw new Exception("Invalid Signature Version specified");
        }
        
        return $this->Sign($stringToSign, $algorithm);
    }
    
    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private function CalculateStringToSignV2(array $parameters)
    {
        $data = 'POST';
        $data .= "\n";
        $data .= "mws.amazonservices.com";
        $data .= "\n";
        $data .= $this->_endpointpath;
        $data .= "\n";
        $data .= $this->GetParametersAsString($parameters);
        return $data;
    }
    
    /**
     * Convert paremeters to Url encoded query string
     */
    private function GetParametersAsString(array $parameters)
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
    private function Sign($data, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception("Non-supported signing method specified");
        }
        
        return base64_encode(hash_hmac($hash, $data, $this->_Config['secretKey'], true));
    }   
    
    /**
     * Formats date as ISO 8601 timestamp
     */
    private function GetFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    /**
     * InvokePost takes the parameters and invokes the _httpPost function to POST the parameters
     * exponential retries on error 500 and 503
     * The response from the POST is an XML which is converted to Array
     */
    private function InvokePost($parameters)
    {
        $response        = array();
        $responseBody    = null;
        $statusCode      = 200;
        /* Submit the request and read response body */
        try {
            $shouldRetry = true;
            $retries     = 0;
            do {
                try {
                    $response        = $this->_httpPost($parameters);
                    $responseBody    = $response['ResponseBody'];
                    $statusCode      = $response['Status'];
		    
                    if ($statusCode == 200) {
                        $shouldRetry = false;
                    } elseif ($statusCode == 500 || $statusCode == 503) {
                        $shouldRetry = ($ResponseToArray['ErrorCode'] === 'RequestThrottled') ? false : true;
                        if ($shouldRetry) {
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
        
        return $responseBody;
    }
    
    /**
     * Perform HTTP post using Curl
     *
     */
    private function _httpPost($parameters)
    {
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_Config['serviceUrl']);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        // if a ca bundle is configured, use it as opposed to the default ca
        // configured for the server
        
        if (!is_null($this->_Config['caBundleFile'])) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->_Config['caBundleFile']);
        }
        
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_UserAgent);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->_Config['ProxyHost'] != null && $this->_Config['ProxyPort'] != -1) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_Config['ProxyHost'] . ':' . $this->_Config['ProxyPort']);
        }
        
        if ($this->_Config['ProxyUsername'] != null && $this->_Config['ProxyPassword'] != null) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_Config['ProxyUsername'] . ':' . $this->_Config['ProxyPassword']);
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
    
    private function createServiceUrl()
    {
	$region = strtolower($this->_Config['region']);
        if (strcasecmp($this->_Config['environment'], self::SANDBOX) == 0) {
            if (array_key_exists($region,$this->MPSregionMappings)) {
                $this->_Config['serviceUrl'] = $this->MPSserviceUrls[$this->MPSregionMappings[$region]] . '/' . self::SANDBOX_PATH . '/' . self::SERVICE_VERSION;
            } else {
		throw new Exception($region.'is not a supported region');
	    }
            
            $this->_endpointpath = '/' . self::SANDBOX_PATH . '/' . self::SERVICE_VERSION;
        } elseif (strcasecmp($this->_Config['environment'], self::LIVE) == 0) {
            if (array_key_exists($region,$this->MPSregionMappings)) {
                $this->_Config['serviceUrl'] = $this->MPSserviceUrls[$this->MPSregionMappings[$region]] . '/' . self::LIVE_PATH . '/' . self::SERVICE_VERSION;
            }
	    else {
		throw new Exception($region.'is not a supported region');
	    }
            
            $this->_endpointpath = '/' . self::LIVE_PATH . '/' . self::SERVICE_VERSION;
        }
    }
    
    private function ProfileEndpointUrl()
    {
	$region = strtolower($this->_Config['UserProfileRegion']);
        if (strcasecmp($this->_Config['environment'], self::SANDBOX) == 0) {
            if (array_key_exists($region,$this->SandboxProfileEndpoint)) {
                $this->ProfileEndpoint = $this->SandboxProfileEndpoint[$region];
            }
        } elseif (strcasecmp($this->_Config['environment'], self::LIVE) == 0) {
            if (array_key_exists($region,$this->LiveProfileEndpoint)) {
                $this->ProfileEndpoint = $this->LiveProfileEndpoint[$region];
            }
        }
    }
    
    private function constructUserAgentHeader($applicationName, $applicationVersion)
    {
        $this->_UserAgent = $this->quoteApplicationName($applicationName) . '/' . $this->quoteApplicationVersion($applicationVersion);
        $this->_UserAgent .= ' (';
        $this->_UserAgent .= 'Language=PHP/' . phpversion();
        $this->_UserAgent .= '; ';
        $this->_UserAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
        $this->_UserAgent .= '; ';
        $this->_UserAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;
        $this->_UserAgent .= ')';
    }
    
    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '/' characters from a string.
     * @param $s
     * @return string
     */
    private function quoteApplicationName($s)
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
    private function quoteApplicationVersion($s)
    {
        $quotedString = preg_replace('/ {2,}|\s/', ' ', $s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\(/', '\\(', $quotedString);
        return $quotedString;
    }
}