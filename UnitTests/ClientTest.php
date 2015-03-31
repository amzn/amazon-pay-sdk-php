<?php

require_once '../OffAmazonPayments/Client.php';
require_once 'Signature.php';

class ClientTest extends PHPUnit_Framework_TestCase
{
    private $_ConfigParams = array(
                'merchant_id' => 'test',
                'access_key' => 'test',
                'secret_key' => "test",
                'currency_code' => 'usd',
                'client_id' => 'test',
                'region' => 'us',
                'sandbox' => true,
                'platform_id' => 'test',
                'cabundle_file' => null,
                'application_name' => 'sdk testing',
                'application_version' => '1.0',
                'proxy_host' => null,
                'proxy_port' => -1,
                'proxy_username' => null,
                'proxy_Password' => null
            );
    public function testConfigArray()
    {
        try {
            
            $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/is not a Json File or the Json File./i', strval($expected));
        }
        
        try {
            
            $ConfigParams = array(
                'a' => 'A',
                'b' => 'B'
            );
            
            $client = new OffAmazonPaymentsService_Client($ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/is either not part of the configuration or has incorrect Key name./i', strval($expected));
        }
        
        try {
            
            $ConfigParams = array();
            
            $client = new OffAmazonPaymentsService_Client($ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/$config cannot be null./i', strval($expected));
        }
    }
    
    public function testJsonFile()
    {
        try {
            
            $ConfigParams = "config.json";
            
            $client = new OffAmazonPaymentsService_Client($ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/Error with message - content is not in json format./i', strval($expected));
        }
        
        try {
            
            $ConfigParams = "abc.json";
            
            $client = new OffAmazonPaymentsService_Client($ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/is not a Json File path or the Json File./i', strval($expected));
        }
    }
    
    public function testSandboxSetter()
    {
        //$ConfigParams = array('sandbox' => true);
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        try {
            $client->setSandbox(true);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/and should be a boolean value./i', strval($expected));
        }
        
        try {
            $client->setSandbox('string value');
        }
        catch (Exception $expected) {
            $this->assertRegExp('/and should be a boolean value./i', strval($expected));
        }
    }
    
    public function testGetOrderReferenceDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'address_consent_token' => 'AddressConsentToken',
            'mws_auth_token' => 'MWSAuthToken'
        );
        
        $action = 'GetOrderReferenceDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        $response = $client->getOrderReferenceDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testSetOrderReferenceDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'amount' 			=> 'OrderReferenceAttributes.OrderTotal.Amount',
            'currency_code' 		=> 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
            'platform_id' 		=> 'OrderReferenceAttributes.PlatformId',
            'seller_note' 		=> 'OrderReferenceAttributes.SellerNote',
            'seller_order_id' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
            'custom_information'	=> 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'SetOrderReferenceDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        $response = $client->setOrderReferenceDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testConfirmOrderReference()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'ConfirmOrderReference';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        $response = $client->confirmOrderReference($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCancelOrderReference()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'cancelation_reason' 	=> 'CancelationReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'CancelOrderReference';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        $response = $client->cancelOrderReference($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCloseOrderReference()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'closure_reason'            => 'ClosureReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'CloseOrderReference';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        $client->closeOrderReference($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCloseAuthorization()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_authorization_id'   => 'AmazonAuthorizationId',
            'closure_reason'            => 'ClosureReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'CloseAuthorization';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        $response = $client->CloseAuthorization($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testAuthorize()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		 => 'SellerId',
            'amazon_order_reference_id'  => 'AmazonOrderReferenceId',
            'authorization_amount' 	 => 'AuthorizationAmount.Amount',
            'currency_code' 		 => 'AuthorizationAmount.CurrencyCode',
            'authorization_reference_id' => 'AuthorizationReferenceId',
            'capture_now' 		 => 'CaptureNow',
            'seller_authorization_note'  => 'SellerAuthorizationNote',
            'transaction_timeout' 	 => 'TransactionTimeout',
            'soft_descriptor' 		 => 'SoftDescriptor',
            'mws_auth_token' 		 => 'MWSAuthToken'
        );
        
        $action = 'Authorize';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->authorize($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testGetAuthorizationDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
         $fieldMappings = array(
            'merchant_id' => 'SellerId',
            'amazon_authorization_id' => 'AmazonAuthorizationId',
            'mws_auth_token' => 'MWSAuthToken'
        );
        
        $action = 'GetAuthorizationDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->getAuthorizationDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCapture()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_authorization_id' 	=> 'AmazonAuthorizationId',
            'capture_amount' 		=> 'CaptureAmount.Amount',
            'currency_code' 		=> 'CaptureAmount.CurrencyCode',
            'capture_reference_id' 	=> 'CaptureReferenceId',
            'seller_capture_note' 	=> 'SellerCaptureNote',
            'soft_descriptor' 		=> 'SoftDescriptor',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'Capture';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->capture($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testGetCaptureDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 	=> 'SellerId',
            'amazon_capture_id' => 'AmazonCaptureId',
            'mws_auth_token' 	=> 'MWSAuthToken'
        );
        
        $action = 'GetCaptureDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->getCaptureDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testRefund()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 	  => 'SellerId',
            'amazon_capture_id'   => 'AmazonCaptureId',
            'refund_reference_id' => 'RefundReferenceId',
            'refund_amount' 	  => 'RefundAmount.Amount',
            'currency_code' 	  => 'RefundAmount.CurrencyCode',
            'seller_refund_note'  => 'SellerRefundNote',
            'soft_descriptor' 	  => 'SoftDescriptor',
            'mws_auth_token' 	  => 'MWSAuthToken'
        );
        
        $action = 'Refund';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->refund($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testGetRefundDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 	=> 'SellerId',
            'amazon_refund_id'  => 'AmazonRefundId',
            'mws_auth_token' 	=> 'MWSAuthToken'
        );
        
        $action = 'GetRefundDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->getRefundDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testGetServiceStatus()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id'    => 'SellerId',
            'mws_auth_token' => 'MWSAuthToken'
        );
        
        $action = 'GetServiceStatus';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->getServiceStatus($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCreateOrderReferenceForId()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'id' 			=> 'Id',
            'id_type' 			=> 'IdType',
            'inherit_shipping_address' 	=> 'InheritShippingAddress',
            'confirm_now' 		=> 'ConfirmNow',
            'amount' 			=> 'OrderReferenceAttributes.OrderTotal.Amount',
            'currency_code' 		=> 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
            'platform_id' 		=> 'OrderReferenceAttributes.PlatformId',
            'seller_note' 		=> 'OrderReferenceAttributes.SellerNote',
            'seller_order_id' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
            'custom_information' 	=> 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );
        
        $action = 'CreateOrderReferenceForId';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->createOrderReferenceForId($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testGetBillingAgreementDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'address_consent_token' 	  => 'AddressConsentToken',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );
        
        $action = 'GetBillingAgreementDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->getBillingAgreementDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testSetBillingAgreementDetails()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'platform_id' 		  => 'BillingAgreementAttributes.PlatformId',
            'seller_note' 		  => 'BillingAgreementAttributes.SellerNote',
            'seller_billing_agreement_id' => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId',
            'custom_information' 	  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation',
            'store_name' 		  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );
        
        $action = 'SetBillingAgreementDetails';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->setBillingAgreementDetails($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testConfirmBillingAgreement()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );
        
        $action = 'ConfirmBillingAgreement';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->confirmBillingAgreement($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testValidateBillingAgreement()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );
        
        $action = 'ValidateBillingAgreement';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->validateBillingAgreement($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testAuthorizeOnBillingAgreement()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 			=> 'SellerId',
            'amazon_billing_agreement_id' 	=> 'AmazonBillingAgreementId',
            'authorization_reference_id' 	=> 'AuthorizationReferenceId',
            'authorization_amount' 		=> 'AuthorizationAmount.Amount',
            'currency_code' 			=> 'AuthorizationAmount.CurrencyCode',
            'seller_authorization_note' 	=> 'SellerAuthorizationNote',
            'transaction_timeout' 		=> 'TransactionTimeout',
            'capture_now' 			=> 'CaptureNow',
            'soft_descriptor' 			=> 'SoftDescriptor',
            'seller_note' 			=> 'SellerNote',
            'platform_id' 			=> 'PlatformId',
            'custom_information' 		=> 'SellerOrderAttributes.CustomInformation',
            'seller_order_id' 			=> 'SellerOrderAttributes.SellerOrderId',
            'store_name' 			=> 'SellerOrderAttributes.StoreName',
            'inherit_shipping_address' 		=> 'InheritShippingAddress',
            'mws_auth_token' 			=> 'MWSAuthToken'
        );
        
        $action = 'AuthorizeOnBillingAgreement';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->authorizeOnBillingAgreement($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCloseBillingAgreement()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'closure_reason' 		  => 'ClosureReason',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );
        
        $action = 'CloseBillingAgreement';
        
        $parameters = $this->_setParameters($fieldMappings,$action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
       
        $expectedStringParams = $this->callPrivateMethod($client,'_calculateSignatureAndParametersToString',$expectedParameters);
        
        
        $response = $client->closeBillingAgreement($apiCallParams);
        
        $apiParametersString = $client->getParameters();
        
        $this->assertEquals($apiParametersString,$expectedStringParams);
    }
    
    public function testCharge()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        $apiCallParams = array('amazon_reference_id' => 'S01-TEST');
            
        $client->charge($apiCallParams);
        
        try {
            
            $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
            $apiCallParams = array('amazon_reference_id' => '');
            
            $client->charge($apiCallParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/amazon_reference_id is null and is a required parameter./i', strval($expected));
        }
        try {
            
            $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
            $apiCallParams = array('amazon_reference_id' => 'T01');
            
            $client->charge($apiCallParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/Invalid Amazon Reference ID./i', strval($expected));
        }
    }
    
    public function testSignature()
    {
        $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
        
        $parameters['SellerId']         = $this->_ConfigParams['merchant_id'];
        $parameters['AWSAccessKeyId']   = $this->_ConfigParams['access_key'];
        $parameters['Version']          = 'test';
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->_getFormattedTimestamp();
        uksort($parameters, 'strcmp');
        
        $signatureObj = new signature($this->_ConfigParams,$parameters);
        $expectedSignature = $signatureObj->getSignature();
        
        $this->callPrivateMethod($client,'_createServiceUrl',null);
        
        $signature = $this->callPrivateMethod($client,'_signParameters',$parameters);
        
        $this->assertEquals($signature,$expectedSignature);
    }
    
    public function test500or503()
    {
       try  {
            $client = new OffAmazonPaymentsService_Client($this->_ConfigParams);
            
            $url = 'https://www.justcharge.me/OffAmazonPayments_Sandbox/2013-01-01';
            $client->setMwsServiceUrl($url);
            $this->callPrivateMethod($client,'_invokePost',null);
            
        }  catch (Exception $expected) {
            $this->assertRegExp('/Maximum number of retry attempts./i', strval($expected));
        }
           
    }
   
    private function _setParameters($fieldMappings,$action)
    {
        $expectedParameters = array();
        $apiCallParams = array();
        
        $parameters = $this->_setDefaultValues($fieldMappings);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];
        
        $expectedParameters['Action'] = $action;
        
        foreach ($fieldMappings as $parm => $value) {
            if(!isset($expectedParameters[$value]))
            {
		$expectedParameters[$value] = 'test';
                $apiCallParams[$parm] = 'test';
            }
        }
        
        return array('expectedParameters' => $expectedParameters,
                     'apiCallParams' =>$apiCallParams);
    }
    
    private function _setDefaultValues($fieldMappings)
    {
        $expectedParameters = array();
        $apiCallParams = array();
        
        if (array_key_exists('platform_id', $fieldMappings)) {
	    $expectedParameters[$fieldMappings['platform_id']] = $this->_ConfigParams['platform_id'];
            $apiCallParams['platform_id'] = $this->_ConfigParams['platform_id'];
	}
        
        if (array_key_exists('currency_code', $fieldMappings)) {
		$expectedParameters[$fieldMappings['currency_code']] = 'TEST';
                $apiCallParams['currency_code'] = 'TEST';
        }
           
        
       return array('expectedParameters' => $expectedParameters,
                    'apiCallParams'      => $apiCallParams);
    }
    
    /**
     * Formats date as ISO 8601 timestamp
     */
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    private function callPrivateMethod($client,$methodName,$parameters)
    {
        $reflection_class = new ReflectionClass("OffAmazonPaymentsService_Client");
        $reflection_method = $reflection_class->getMethod($methodName);
        $reflection_method->setAccessible(true);
        $expectedStringParams = $reflection_method->invoke($client,$parameters);
        return $expectedStringParams;
    }    
}