
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
		'clientId'           => null
	);
    private $_serviceUrls = array('eu' => 'https://mws-eu.amazonservices.com',
                                  'na' => 'https://mws.amazonservices.com');
    
    private $LiveProfileEndpoint = array('uk' => 'https://api.amazon.co.uk',
					 'na' => 'https://api.amazon.com',
					 'de' => 'https://api.amazon.co.de');
    
    private $SandboxProfileEndpoint = array('uk' => 'https://api.sandbox.amazon.co.uk',
					    'na' => 'https://api.sandbox.amazon.com',
					    'de' => 'https://api.sandbox.amazon.co.de');
    
    private $_regionMappings = array('de' => 'eu',
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
            throw new Exception("Key " . $name . " is not part of the configuration", 1);
	    }
	}
	
        if ($this->_Config['SellerId'] == "") {
            throw new InvalidArgumentException("merchantId not set in the properties file");
        }
        
        if ($this->_Config['accessKey'] == "") {
            throw new InvalidArgumentException("accessKey not set ");
        }
        
        if ($this->_Config['secretKey'] == "") {
            throw new InvalidArgumentException("secretKey not set in the properties file");
        }
        
        if ($this->_Config['region'] == "") {
            throw new InvalidArgumentException("region not set in the properties file");
        }
        
        if ($this->_Config['environment'] == "") {
            throw new InvalidArgumentException("environment not set in the properties file");
        }
    }
    
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_Config)) {
            $this->_Config[$name] = $value;
        } else {
            throw new Exception("Key " . $name . " is not part of the configuration", 1);
        }
    }
    
    public function __get($name)
    {
        if (array_key_exists($name, $this->_Config)) {
            return $this->_Config[$name];
        } else {
            throw new Exception("Key " . $name . " was not found in the configuration", 1);
        }
    }
    
    public function GetUserInfo($access_token)
    {
	$this->ProfileEndpointUrl();
	
	$c = curl_init($this->ProfileEndpoint.'/auth/o2/tokeninfo?access_token='. urlencode($access_token));
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($c);
	curl_close($c);
	$data = json_decode($response);
	
	if ($data->aud != $this->_Config['clientId']) {
	// the access token does not belong to us
	header('HTTP/1.1 404 Not Found');
	throw new Exception('The Requested Information was not found');
	}

	// exchange the access token for user profile
	$c = curl_init($this->ProfileEndpoint.'/user/profile');
	curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer '. $access_token));
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($c);
	curl_close($c);
	$UserInfoObject = json_decode($response);
	return $UserInfoObject;
    }
    
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
    
    /* Returns the operational status of the Off-Amazon Payments API section
     *The GetServiceStatus operation returns the operational status of the Off-Amazon Payments API
     *section of Amazon Marketplace Web Service (Amazon MWS). Status values are GREEN, GREEN_I, YELLOW, and RED.
     *@see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetServiceStatus.html
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
    
    /* Creates an order reference for the given object
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CreateOrderReferenceForId.html
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
        if (!empty($RequestParameters['MWSAuthToken']))
            $parameters['MWSAuthToken'] = $RequestParameters['MWSAuthToken'];
        $ResponseToArray = $this->CalculatesignatureAndPost($parameters);
        return ($ResponseToArray);
    }
    
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
    
    private function CalculatesignatureAndPost($parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_Config['accessKey'];
        $parameters['Version']          = self::SERVICE_VERSION;
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->_getFormattedTimestamp();
        uksort($parameters, 'strcmp');
	$this->createServiceUrl();
        $parameters['Signature'] = $this->_signParameters($parameters);
        $parameters              = $this->_getParametersAsString($parameters);
        $ResponseToArray         = $this->_invokePost($parameters);
        return $ResponseToArray;
    }
    
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
    
    private function _calculateStringToSignV2(array $parameters)
    {
        $data = 'POST';
        $data .= "\n";
        $data .= "mws.amazonservices.com";
        $data .= "\n";
        $data .= $this->_endpointpath;
        $data .= "\n";
        $data .= $this->_getParametersAsString($parameters);
        return $data;
    }
    
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
    
    private function _sign($data, $algorithm)
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
    
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    private function _invokePost($parameters)
    {
        $response        = array();
        $responseBody    = null;
        $ResponseToArray = null;
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
                    $ResponseToArray = simplexml_load_string((string) $responseBody);
                    $ResponseToArray = json_encode($ResponseToArray);
                    $ResponseToArray = json_decode($ResponseToArray, true);
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
        
        return $ResponseToArray;
    }
    
    /**
     * Perform HTTP post with exponential retries on error 500 and 503
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
        /* $headers = array();
        foreach ($other as $value) {
        if (strpos($value, ': ') !== FALSE) {
        list ($k, $v) = explode (': ', $value);
        if (array_key_exists($k, $headers)) {
        $headers[$k] = $headers[$k] . "," . $v;
        } else {
        $headers[$k] = $v;
        }
        }
        }*/
        /*require_once 'OffAmazonPaymentsService/Model/ResponseHeaderMetadata.php';
        $responseHeaderMetadata = new OffAmazonPaymentsService_Model_ResponseHeaderMetadata(
        $headers['x-mws-request-id'],
        $headers['x-mws-response-context'],
        $headers['x-mws-timestamp']);
        */
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
            if (array_key_exists($region,$this->_regionMappings)) {
                $this->_Config['serviceUrl'] = $this->_serviceUrls[$this->_regionMappings[$region]] . '/' . self::SANDBOX_PATH . '/' . self::SERVICE_VERSION;
            } else {
		throw new Exception($region.'is not a supported region');
	    }
            
            $this->_endpointpath = '/' . self::SANDBOX_PATH . '/' . self::SERVICE_VERSION;
        } elseif (strcasecmp($this->_Config['environment'], self::LIVE) == 0) {
            if (array_key_exists($region,$this->_regionMappings)) {
                $this->_Config['serviceUrl'] = $this->_serviceUrls[$this->_regionMappings[$region]] . '/' . self::LIVE_PATH . '/' . self::SERVICE_VERSION;
            }
	    else {
		throw new Exception($region.'is not a supported region');
	    }
            
            $this->_endpointpath = '/' . self::LIVE_PATH . '/' . self::SERVICE_VERSION;
        }
    }
    
    private function ProfileEndpointUrl()
    {
	$region = strtolower($this->_Config['region']);
        if (strcasecmp($this->_Config['environment'], self::SANDBOX) == 0) {
            if (array_key_exists($region,$this->_regionMappings)) {
                $this->ProfileEndpoint = $this->SandboxProfileEndpoint[$region];
            }
        } elseif (strcasecmp($this->_Config['environment'], self::LIVE) == 0) {
            if (array_key_exists($region,$this->_regionMappings)) {
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