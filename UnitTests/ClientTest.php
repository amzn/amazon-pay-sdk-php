<?php
namespace PayWithAmazon;

require_once '../PayWithAmazon/Client.php';
require_once '../PayWithAmazon/ResponseParser.php';
require_once 'Signature.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $configParams = array(
                'merchant_id' => 'test',
                'access_key' => 'test',
                'secret_key' => "test",
                'currency_code' => 'usd',
                'client_id' => 'test',
                'region' => 'us',
                'sandbox' => true,
                'platform_id' => 'test',
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
            $client = new Client($this->configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/is not a Json File or the Json File./i', strval($expected));
        }

        try {
            $configParams = array(
                'a' => 'A',
                'b' => 'B'
            );
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/is either not part of the configuration or has incorrect Key name./i', strval($expected));
        }

        try {
            $configParams = array();
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/$config cannot be null./i', strval($expected));
        }
    }

    public function testJsonFile()
    {
        try {
            $configParams = "config.json";
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/Error with message - content is not in json format./i', strval($expected));
        }

        try {
            $configParams = "abc.json";
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/is not a Json File path or the Json File./i', strval($expected));
        }
    }

    public function testSandboxSetter()
    {
        $client = new Client($this->configParams);
        try {
            $client->setSandbox(true);
        } catch (\Exception $expected) {
            $this->assertRegExp('/and should be a boolean value./i', strval($expected));
        }

        try {
            $client->setSandbox('string value');
        } catch (\Exception $expected) {
            $this->assertRegExp('/and should be a boolean value./i', strval($expected));
        }
    }

    public function testGetOrderReferenceDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'address_consent_token' => 'AddressConsentToken',
            'mws_auth_token' => 'MWSAuthToken'
        );

        $action = 'GetOrderReferenceDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getOrderReferenceDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testSetOrderReferenceDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'Merchant_Id' 		=> 'SellerId',
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setOrderReferenceDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testConfirmOrderReference()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token' => 'MWSAuthToken',
            'success_url' => 'SuccessUrl',
            'failure_url' => 'FailureUrl'
        );

        $action = 'ConfirmOrderReference';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->confirmOrderReference($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCancelOrderReference()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'cancelation_reason' 	=> 'CancelationReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );

        $action = 'CancelOrderReference';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->cancelOrderReference($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCloseOrderReference()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'closure_reason'            => 'ClosureReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );

        $action = 'CloseOrderReference';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->closeOrderReference($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCloseAuthorization()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		=> 'SellerId',
            'amazon_authorization_id'   => 'AmazonAuthorizationId',
            'closure_reason'            => 'ClosureReason',
            'mws_auth_token' 		=> 'MWSAuthToken'
        );

        $action = 'CloseAuthorization';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->CloseAuthorization($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testAuthorize()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->authorize($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testGetAuthorizationDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' => 'SellerId',
            'amazon_authorization_id' => 'AmazonAuthorizationId',
            'mws_auth_token' => 'MWSAuthToken'
        );

        $action = 'GetAuthorizationDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getAuthorizationDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCapture()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->capture($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testGetCaptureDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 	=> 'SellerId',
            'amazon_capture_id' => 'AmazonCaptureId',
            'mws_auth_token' 	=> 'MWSAuthToken'
        );

        $action = 'GetCaptureDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getCaptureDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testRefund()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->refund($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testGetRefundDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 	=> 'SellerId',
            'amazon_refund_id'  => 'AmazonRefundId',
            'mws_auth_token' 	=> 'MWSAuthToken'
        );

        $action = 'GetRefundDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getRefundDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testGetServiceStatus()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'    => 'SellerId',
            'mws_auth_token' => 'MWSAuthToken'
        );

        $action = 'GetServiceStatus';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getServiceStatus($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCreateOrderReferenceForId()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->createOrderReferenceForId($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testGetBillingAgreementDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'address_consent_token' 	  => 'AddressConsentToken',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );

        $action = 'GetBillingAgreementDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getBillingAgreementDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testSetBillingAgreementDetails()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setBillingAgreementDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testConfirmBillingAgreement()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );

        $action = 'ConfirmBillingAgreement';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->confirmBillingAgreement($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testValidateBillingAgreement()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );

        $action = 'ValidateBillingAgreement';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->validateBillingAgreement($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testAuthorizeOnBillingAgreement()
    {
        $client = new Client($this->configParams);
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

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->authorizeOnBillingAgreement($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCloseBillingAgreement()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id' 		  => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'closure_reason' 		  => 'ClosureReason',
            'mws_auth_token' 		  => 'MWSAuthToken'
        );

        $action = 'CloseBillingAgreement';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->closeBillingAgreement($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testCharge()
    {
        $client = new Client($this->configParams);
        $apiCallParams = array('amazon_reference_id' => 'S01-TEST');


        try {
            $client = new Client($this->configParams);
            $apiCallParams = array('amazon_reference_id' => '');
            $client->charge($apiCallParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/key amazon_order_reference_id or amazon_billing_agreement_id is null and is a required parameter./i', strval($expected));
        }

        try {
            $client = new Client($this->configParams);
            $apiCallParams = array('amazon_reference_id' => 'T01');
            $client->charge($apiCallParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/Invalid Amazon Reference ID./i', strval($expected));
        }
    }

    public function testGetUserInfo()
    {
        try {
            $this->configParams['region'] = '';
            $client = new Client($this->configParams);
            $client->getUserInfo('Atza');
        } catch (\Exception $expected) {
            $this->assertRegExp('/is a required parameter./i', strval($expected));
        }

        try {
            $this->configParams['region'] = 'us';
            $client = new Client($this->configParams);
            $client->getUserInfo(null);
        } catch (\Exception $expected) {
            $this->assertRegExp('/Access Token is a required parameter and is not set./i', strval($expected));
        }
    }

    public function testSignature()
    {
        $client = new Client($this->configParams);

        $parameters['SellerId']         = $this->configParams['merchant_id'];
        $parameters['AWSAccessKeyId']   = $this->configParams['access_key'];
        $parameters['Version']          = 'test';
        $parameters['SignatureMethod']  = 'HmacSHA256';
        $parameters['SignatureVersion'] = 2;
        $parameters['Timestamp']        = $this->getFormattedTimestamp();
        uksort($parameters, 'strcmp');

        $signatureObj = new Signature($this->configParams,$parameters);
        $expectedSignature = $signatureObj->getSignature();

        $this->callPrivateMethod($client,'createServiceUrl', null);

        $signature = $this->callPrivateMethod($client,'signParameters', $parameters);

        $this->assertEquals($signature, $expectedSignature);
    }

    public function test500or503()
    {
       try  {
            $client = new Client($this->configParams);

            $url = 'https://www.justcharge.me/OffAmazonPayments_Sandbox/2013-01-01';
            $client->setMwsServiceUrl($url);
            $this->callPrivateMethod($client, 'invokePost', null);

        } catch (\Exception $expected) {
            $this->assertRegExp('/Maximum number of retry attempts./i', strval($expected));
        }

    }

    public function testXmlResponse()
    {
        $response = array();
        $response['ResponseBody'] =
        '<GetOrderReferenceDetailsResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
        <AmazonOrderReferenceId>S01-5806490-2147504</AmazonOrderReferenceId>
        <ExpirationTimestamp>2015-09-27T02:18:33.408Z</ExpirationTimestamp>
        <SellerNote>This is testing API call</SellerNote>
        </GetOrderReferenceDetailsResponse>';

        $responseObj = new ResponseParser($response);
        $xmlResponse = $responseObj->toXml();

        $this->assertEquals($xmlResponse, $response['ResponseBody']);
    }

    public function testJsonResponse()
    {
        $response = array('Status' => '200');
        $response['ResponseBody'] =
        '<GetOrderReferenceDetailsResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
        <AmazonOrderReferenceId>S01-5806490-2147504</AmazonOrderReferenceId>
        <ExpirationTimestamp>2015-09-27T02:18:33.408Z</ExpirationTimestamp>
        <SellerNote>This is testing API call</SellerNote>
        </GetOrderReferenceDetailsResponse>';

        $json =
        '{"AmazonOrderReferenceId":"S01-5806490-2147504","ExpirationTimestamp":"2015-09-27T02:18:33.408Z","SellerNote":"This is testing API call","ResponseStatus":"200"}';

        $responseObj = new ResponseParser($response);
        $jsonResponse = $responseObj->toJson();

        $this->assertEquals($json, $jsonResponse);
    }

    public function testArrayResponse()
    {
        $response = array('Status' => '200');
        $response['ResponseBody'] =
        '<GetOrderReferenceDetailsResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
        <AmazonOrderReferenceId>S01-5806490-2147504</AmazonOrderReferenceId>
        <ExpirationTimestamp>2015-09-27T02:18:33.408Z</ExpirationTimestamp>
        <SellerNote>This is testing API call</SellerNote>
        </GetOrderReferenceDetailsResponse>';

        $array = array('AmazonOrderReferenceId' => 'S01-5806490-2147504',
                      'ExpirationTimestamp' => '2015-09-27T02:18:33.408Z',
                      'SellerNote' => 'This is testing API call',
                      'ResponseStatus' => '200');

        $responseObj = new ResponseParser($response);
        $arrayResponse = $responseObj->toArray();

        $this->assertEquals($array, $arrayResponse);
    }

    private function setParametersAndPost($fieldMappings, $action)
    {
        $expectedParameters = array();
        $apiCallParams = array();

        $parameters = $this->setDefaultValues($fieldMappings);
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
                     'apiCallParams'      =>$apiCallParams);
    }

    private function setDefaultValues($fieldMappings)
    {
        $expectedParameters = array();
        $apiCallParams = array();

        if (array_key_exists('platform_id', $fieldMappings)) {
	    $expectedParameters[$fieldMappings['platform_id']] = $this->configParams['platform_id'];
            $apiCallParams['platform_id'] = $this->configParams['platform_id'];
	}

        if (array_key_exists('currency_code', $fieldMappings)) {
	    $expectedParameters[$fieldMappings['currency_code']] = 'TEST';
            $apiCallParams['currency_code'] = 'TEST';
        }

        return array('expectedParameters' => $expectedParameters,
                     'apiCallParams'      => $apiCallParams);
    }

    /* Formats date as ISO 8601 timestamp */

    private function getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }

    private function callPrivateMethod($client, $methodName, $parameters)
    {
        $reflectionClass = new \ReflectionClass("PayWithAmazon\Client");
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        $expectedStringParams = $reflectionMethod->invoke($client, $parameters);
        return $expectedStringParams;
    }
}
