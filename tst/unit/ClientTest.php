<?php
namespace AmazonPay;

require_once 'AmazonPay/Client.php';
require_once 'AmazonPay/ResponseParser.php';
require_once 'Signature.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $configParams = array(
                'merchant_id'          => 'MERCHANT1234567',
                'access_key'           => 'ABCDEFGHI1JKLMN2O7',
                'secret_key'           => "abc123Def456gHi789jKLmpQ987rstu6vWxyz",
                'currency_code'        => 'usd',
                'client_id'            => 'amzn1.application-oa2-client.45789c45a8f34927830be1d9e029f480',
                'region'               => 'us',
                'sandbox'              => true,
                'platform_id'          => 'test',
                'application_name'     => 'sdk testing',
                'application_version'  => '1.0',
                'proxy_host'           => null,
                'proxy_port'           => -1,
                'proxy_username'       => null,
                'proxy_Password'       => null
            );

    public function testConfigArray()
    {
        // Test that trimmimg isn't converting the Boolean to a string
        $client = new Client($this->configParams);
        $this->assertTrue((bool)$client->__get('sandbox'));

        // Test four cases in which sandbox is in constructor with an array
        $client = new Client(array('sandbox' => false));
        $this->assertFalse((bool)$client->__get('sandbox'));

        try {
          $client = new Client(array('sandbox' => 'false'));
        } catch (\Exception $expected) {
            $this->assertRegExp('/should be a boolean value/i', strval($expected));
        }

        $client = new Client(array('sandbox' => true));
        $this->assertTrue((bool)$client->__get('sandbox'));

        try {
            $client = new Client(array('sandbox' => 'true'));
        } catch (\Exception $expected) {
            $this->assertRegExp('/should be a boolean value/i', strval($expected));
        }

        // Test that string trimming is working as intended
        $client = new Client(array(
            'region'        => 'us  ', // two spaces at end
            'currency_code' => '  usd', // two spaces at beginning
            'client_id'     => '  A113  ' // two spaces and beginning and end
        ));
        $this->assertEquals('us', $client->__get('region'));
        $this->assertEquals('usd', $client->__get('currency_code'));
        $this->assertEquals('A113', $client->__get('client_id'));
        $this->assertFalse((bool)$client->__get('sandbox'));

        // Unclear what is is actually doing, exception doesn't get thrown, consider removing
        try {
            $client = new Client($this->configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/is not a Json File or the Json File./i', strval($expected));
        }

        // Test passing in invalid keys to constructor
        try {
            $configParams = array(
                'a' => 'A',
                'b' => 'B'
            );
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/is either not part of the configuration or has incorrect Key name./i', strval($expected));
        }

        // Test passing in override service URL for MWS API endpoint
        $client = new Client(array('override_service_url' => 'https://over.ride'));
        $this->assertEquals('https://over.ride', $client->__get('override_service_url'));

        // Test passing in an empty array to construtor
        try {
            $configParams = array();
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/$config cannot be null./i', strval($expected));
        }

    }

    public function testJsonFile()
    {
        $configParams = "tst/unit/config/sandbox_true_bool.json";
        $client = new Client($configParams);

        $this->assertTrue((bool)$client->__get('sandbox'));
        $this->assertEquals('test_merchant_id', $client->__get('merchant_id'));
        $this->assertEquals('test_access_key', $client->__get('access_key'));
        $this->assertEquals('test_secret_key', $client->__get('secret_key'));
        $this->assertEquals('USD', $client->__get('currency_code'));
        $this->assertEquals('test_client_id', $client->__get('client_id'));
        $this->assertEquals('us', $client->__get('region'));
        $this->assertEquals('sdk testing', $client->__get('application_name'));
        $this->assertEquals('1.0', $client->__get('application_version'));

        try {
            $configParams = "tst/unit/config/sandbox_true_string.json";
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/should be a boolean value/i', strval($expected));
        }

        $configParams = "tst/unit/config/sandbox_false_bool.json";
        $client = new Client($configParams);
        $this->assertFalse((bool)$client->__get('sandbox'));

        $configParams = "tst/unit/config/sandbox_none.json";
        $client = new Client($configParams);
        $this->assertFalse((bool)$client->__get('sandbox'));

        try {
            $configParams = "tst/unit/config/sandbox_false_string.json";
            $client = new Client($configParams);
        } catch (\Exception $expected) {
            $this->assertRegExp('/should be a boolean value/i', strval($expected));
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
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'address_consent_token'     => 'AddressConsentToken',
            'access_token'              => 'AccessToken',
            'mws_auth_token'            => 'MWSAuthToken'
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

    public function testListOrderReference()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'        => 'SellerId',
            'mws_auth_token'     => 'MWSAuthToken',
            
            'query_id'           => 'QueryId',
            'query_id_type'      => 'QueryIdType',
            'page_size'          => 'PageSize',
            'created_start_time' => 'CreatedTimeRange.StartTime',
            'created_end_time'   => 'CreatedTimeRange.EndTime',
            'sort_order'         => 'SortOrder',
            'order_status_list'  => array()

        );

        $action = 'ListOrderReference';
        $parameters = $this->setParametersAndPost($fieldMappings, $action);

        $expectedParameters = $parameters['expectedParameters'];
        $expectedParameters['OrderReferenceStatusListFilter.OrderReferenceStatus.1'] = 'Open';
        $expectedParameters['OrderReferenceStatusListFilter.OrderReferenceStatus.2'] = 'Closed';
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->listOrderReference($apiCallParams);

        $apiParametersString = $client->getParameters();

        // Hack to remove mismatched Signature (due to param mismatch), then remove Signature from both to eliminate mismatch
        $apiParametersString = preg_replace("/&PaymentDomain=[^&]*/", "", $apiParametersString);
        $apiParametersString = preg_replace("/&Signature=[^&]*/", "", $apiParametersString);
        $expectedStringParams = preg_replace("/&Signature=[^&]*/", "", $expectedStringParams);

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testListOrderReferenceByNextToken()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'     => 'SellerId',
            'mws_auth_token'  => 'MWSAuthToken',
            
            'next_page_token' => 'NextPageToken'
        );

        $action = 'ListOrderReferenceByNextToken';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);


        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->listOrderReferenceByNextToken($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testSetOrderReferenceDetails()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'Merchant_Id'                   => 'SellerId',
            'amazon_order_reference_id'     => 'AmazonOrderReferenceId',
            'amount'                        => 'OrderReferenceAttributes.OrderTotal.Amount',
            'currency_code'                 => 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
            'platform_id'                   => 'OrderReferenceAttributes.PlatformId',
            'seller_note'                   => 'OrderReferenceAttributes.SellerNote',
            'seller_order_id'               => 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name'                    => 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
            'custom_information'            => 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
            'supplementary_data'            => 'OrderReferenceAttributes.SellerOrderAttributes.SupplementaryData',
            'request_payment_authorization' => 'OrderReferenceAttributes.RequestPaymentAuthorization',
            'mws_auth_token'                => 'MWSAuthToken'
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

    public function testSetOrderAttributesBeforeConfirm()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                       => 'SellerId',
            'amazon_order_reference_id'         => 'AmazonOrderReferenceId',
            'amount'                            => 'OrderAttributes.OrderTotal.Amount',
            'currency_code'                     => 'OrderAttributes.OrderTotal.CurrencyCode',
            'platform_id'                       => 'OrderAttributes.PlatformId',
            'seller_note'                       => 'OrderAttributes.SellerNote',
            'seller_order_id'                   => 'OrderAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name'                        => 'OrderAttributes.SellerOrderAttributes.StoreName',
            'custom_information'                => 'OrderAttributes.SellerOrderAttributes.CustomInformation',
            'supplementary_data'                => 'OrderAttributes.SellerOrderAttributes.SupplementaryData',
            'request_payment_authorization'     => 'OrderAttributes.RequestPaymentAuthorization',
            'payment_service_provider_id'       => 'OrderAttributes.PaymentServiceProviderAttributes.PaymentServiceProviderId',
            'payment_service_provider_order_id' => 'OrderAttributes.PaymentServiceProviderAttributes.PaymentServiceProviderOrderId',
            'order_item_categories'             => array(),
            'mws_auth_token'                    => 'MWSAuthToken'
        );

        $action = 'SetOrderAttributes';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $expectedParameters['OrderAttributes.SellerOrderAttributes.OrderItemCategories.OrderItemCategory.1'] = 'Antiques';
        $expectedParameters['OrderAttributes.SellerOrderAttributes.OrderItemCategories.OrderItemCategory.2'] = 'Electronics';
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setOrderAttributes($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }


    /* Call is same as BeforeConfirm call except the amount and currency_code fields are omitted */
    public function testSetOrderAttributesAfterConfirm()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                       => 'SellerId',
            'amazon_order_reference_id'         => 'AmazonOrderReferenceId',
            'platform_id'                       => 'OrderAttributes.PlatformId',
            'seller_note'                       => 'OrderAttributes.SellerNote',
            'seller_order_id'                   => 'OrderAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name'                        => 'OrderAttributes.SellerOrderAttributes.StoreName',
            'custom_information'                => 'OrderAttributes.SellerOrderAttributes.CustomInformation',
            'request_payment_authorization'     => 'OrderAttributes.RequestPaymentAuthorization',
            'payment_service_provider_id'       => 'OrderAttributes.PaymentServiceProviderAttributes.PaymentServiceProviderId',
            'payment_service_provider_order_id' => 'OrderAttributes.PaymentServiceProviderAttributes.PaymentServiceProviderOrderId',
            'order_item_categories'             => array(),
            'mws_auth_token'                    => 'MWSAuthToken'
        );

        $action = 'SetOrderAttributes';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $expectedParameters['OrderAttributes.SellerOrderAttributes.OrderItemCategories.OrderItemCategory.1'] = 'Antiques';
        $expectedParameters['OrderAttributes.SellerOrderAttributes.OrderItemCategories.OrderItemCategory.2'] = 'Electronics';
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setOrderAttributes($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testConfirmOrderReferenceWithAllSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token'            => 'MWSAuthToken',
            'success_url'               => 'SuccessUrl',
            'failure_url'               => 'FailureUrl',
            'authorization_amount'      => 'AuthorizationAmount.Amount',
            'currency_code'             => 'AuthorizationAmount.CurrencyCode'
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

    public function testConfirmOrderReferenceWithAllButCurrencyCodeSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token'            => 'MWSAuthToken',
            'success_url'               => 'SuccessUrl',
            'failure_url'               => 'FailureUrl',
            'authorization_amount'      => 'AuthorizationAmount.Amount'
        );

        $action = 'ConfirmOrderReference';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedParameters['AuthorizationAmount.CurrencyCode'] = 'USD'; # default from client
        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->confirmOrderReference($apiCallParams);

        $apiParametersString = $client->getParameters();
        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testConfirmOrderReferenceWithUrlSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token'            => 'MWSAuthToken',
            'success_url'               => 'SuccessUrl',
            'failure_url'               => 'FailureUrl'
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

    public function testConfirmOrderReferenceWithoutSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'mws_auth_token'            => 'MWSAuthToken'
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
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'cancelation_reason'        => 'CancelationReason',
            'mws_auth_token'            => 'MWSAuthToken'
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
            'merchant_id'               => 'SellerId',
            'amazon_order_reference_id' => 'AmazonOrderReferenceId',
            'closure_reason'            => 'ClosureReason',
            'mws_auth_token'            => 'MWSAuthToken'
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
            'merchant_id'             => 'SellerId',
            'amazon_authorization_id' => 'AmazonAuthorizationId',
            'closure_reason'          => 'ClosureReason',
            'mws_auth_token'          => 'MWSAuthToken'
        );

        $action = 'CloseAuthorization';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->closeAuthorization($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testAuthorize()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                => 'SellerId',
            'amazon_order_reference_id'  => 'AmazonOrderReferenceId',
            'authorization_amount'       => 'AuthorizationAmount.Amount',
            'currency_code'              => 'AuthorizationAmount.CurrencyCode',
            'authorization_reference_id' => 'AuthorizationReferenceId',
            'capture_now'                => 'CaptureNow',
            'seller_authorization_note'  => 'SellerAuthorizationNote',
            'transaction_timeout'        => 'TransactionTimeout',
            'soft_descriptor'            => 'SoftDescriptor',
            'mws_auth_token'             => 'MWSAuthToken'
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
            'merchant_id'             => 'SellerId',
            'amazon_authorization_id' => 'AmazonAuthorizationId',
            'mws_auth_token'          => 'MWSAuthToken'
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
            'merchant_id'             => 'SellerId',
            'amazon_authorization_id' => 'AmazonAuthorizationId',
            'capture_amount'          => 'CaptureAmount.Amount',
            'currency_code'           => 'CaptureAmount.CurrencyCode',
            'capture_reference_id'    => 'CaptureReferenceId',
            'seller_capture_note'     => 'SellerCaptureNote',
            'soft_descriptor'         => 'SoftDescriptor',
            'mws_auth_token'          => 'MWSAuthToken'
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
            'merchant_id'       => 'SellerId',
            'amazon_capture_id' => 'AmazonCaptureId',
            'mws_auth_token'    => 'MWSAuthToken'
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
            'merchant_id'         => 'SellerId',
            'amazon_capture_id'   => 'AmazonCaptureId',
            'refund_reference_id' => 'RefundReferenceId',
            'refund_amount'       => 'RefundAmount.Amount',
            'currency_code'       => 'RefundAmount.CurrencyCode',
            'seller_refund_note'  => 'SellerRefundNote',
            'soft_descriptor'     => 'SoftDescriptor',
            'mws_auth_token'      => 'MWSAuthToken'
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
            'merchant_id'      => 'SellerId',
            'amazon_refund_id' => 'AmazonRefundId',
            'mws_auth_token'   => 'MWSAuthToken'
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

    public function testGetMerchantAccountStatus()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'    => 'SellerId',
            'mws_auth_token' => 'MWSAuthToken'
        );

        $action = 'GetMerchantAccountStatus';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getMerchantAccountStatus($apiCallParams);

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
            'merchant_id'              => 'SellerId',
            'id'                       => 'Id',
            'id_type'                  => 'IdType',
            'inherit_shipping_address' => 'InheritShippingAddress',
            'confirm_now'              => 'ConfirmNow',
            'amount'                   => 'OrderReferenceAttributes.OrderTotal.Amount',
            'currency_code'            => 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
            'platform_id'              => 'OrderReferenceAttributes.PlatformId',
            'seller_note'              => 'OrderReferenceAttributes.SellerNote',
            'seller_order_id'          => 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
            'store_name'               => 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
            'custom_information'       => 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
            'mws_auth_token'           => 'MWSAuthToken'
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
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'address_consent_token'       => 'AddressConsentToken',
            'access_token'                => 'AccessToken',
            'mws_auth_token'              => 'MWSAuthToken'
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

    public function testSetBillingAgreementDetailsWithoutSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'platform_id'                 => 'BillingAgreementAttributes.PlatformId',
            'seller_note'                 => 'BillingAgreementAttributes.SellerNote',
            'seller_billing_agreement_id' => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId',
            'custom_information'          => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation',
            'store_name'                  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName',
            'mws_auth_token'              => 'MWSAuthToken'
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

    public function testSetBillingAgreementDetailsWithSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'platform_id'                 => 'BillingAgreementAttributes.PlatformId',
            'seller_note'                 => 'BillingAgreementAttributes.SellerNote',
            'seller_billing_agreement_id' => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId',
            'custom_information'          => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation',
            'store_name'                  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName',
            'billing_agreement_type'      => 'BillingAgreementAttributes.BillingAgreementType',
            'subscription_amount'         => 'BillingAgreementAttributes.SubscriptionAmount.Amount',
            'currency_code'               => 'BillingAgreementAttributes.SubscriptionAmount.CurrencyCode',
            'mws_auth_token'              => 'MWSAuthToken'
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

    public function testSetBillingAgreementDetailsWithSCAExceptCurrencyCode()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'platform_id'                 => 'BillingAgreementAttributes.PlatformId',
            'seller_note'                 => 'BillingAgreementAttributes.SellerNote',
            'seller_billing_agreement_id' => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId',
            'custom_information'          => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation',
            'store_name'                  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName',
            'billing_agreement_type'      => 'BillingAgreementAttributes.BillingAgreementType',
            'subscription_amount'         => 'BillingAgreementAttributes.SubscriptionAmount.Amount',
            'mws_auth_token'              => 'MWSAuthToken'
        );

        $action = 'SetBillingAgreementDetails';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedParameters['BillingAgreementAttributes.SubscriptionAmount.CurrencyCode'] = 'USD'; # default from client
        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setBillingAgreementDetails($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testConfirmBillingAgreementWithoutSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token'              => 'MWSAuthToken'
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

    public function testConfirmBillingAgreementWithSCA()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'success_url'                 => 'SuccessUrl',
            'failure_url'                 => 'FailureUrl',
            'mws_auth_token'              => 'MWSAuthToken'
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
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'mws_auth_token'              => 'MWSAuthToken'
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
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'authorization_reference_id'  => 'AuthorizationReferenceId',
            'authorization_amount'        => 'AuthorizationAmount.Amount',
            'currency_code'               => 'AuthorizationAmount.CurrencyCode',
            'seller_authorization_note'   => 'SellerAuthorizationNote',
            'transaction_timeout'         => 'TransactionTimeout',
            'capture_now'                 => 'CaptureNow',
            'soft_descriptor'             => 'SoftDescriptor',
            'seller_note'                 => 'SellerNote',
            'platform_id'                 => 'PlatformId',
            'custom_information'          => 'SellerOrderAttributes.CustomInformation',
            'seller_order_id'             => 'SellerOrderAttributes.SellerOrderId',
            'store_name'                  => 'SellerOrderAttributes.StoreName',
            'inherit_shipping_address'    => 'InheritShippingAddress',
            'mws_auth_token'              => 'MWSAuthToken'
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
            'merchant_id'                 => 'SellerId',
            'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
            'closure_reason'              => 'ClosureReason',
            'mws_auth_token'              => 'MWSAuthToken'
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

    public function testGetMerchantNotificationConfiguration()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                 => 'SellerId',
            'mws_auth_token'              => 'MWSAuthToken'
        );

        $action = 'GetMerchantNotificationConfiguration';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->getMerchantNotificationConfiguration($apiCallParams);

        $apiParametersString = $client->getParameters();

        $this->assertEquals($apiParametersString, $expectedStringParams);
    }

    public function testSetMerchantNotificationConfiguration()
    {
        $client = new Client($this->configParams);
        $fieldMappings = array(
            'merchant_id'                     => 'SellerId',
            'notification_configuration_list' => array(),
            'mws_auth_token'                  => 'MWSAuthToken'
        );

        $action = 'SetMerchantNotificationConfiguration';

        $parameters = $this->setParametersAndPost($fieldMappings, $action);
        $expectedParameters = $parameters['expectedParameters'];
        $expectedParameters['NotificationConfigurationList.NotificationConfiguration.1.NotificationUrl'] = 'https://dev.null/one';
        $expectedParameters['NotificationConfigurationList.NotificationConfiguration.2.NotificationUrl'] = 'https://dev.null/two';
        $expectedParameters['NotificationConfigurationList.NotificationConfiguration.1.EventTypes.EventTypeList.1'] = 'ORDER_REFERENCE';
        $expectedParameters['NotificationConfigurationList.NotificationConfiguration.1.EventTypes.EventTypeList.2'] = 'PAYMENT_AUTHORIZE';
        $expectedParameters['NotificationConfigurationList.NotificationConfiguration.2.EventTypes.EventTypeList.1'] = 'ALL';
        $apiCallParams = $parameters['apiCallParams'];

        $expectedStringParams = $this->callPrivateMethod($client, 'calculateSignatureAndParametersToString', $expectedParameters);

        $response = $client->setMerchantNotificationConfiguration($apiCallParams);

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

            $url = 'https://www.amazon.com/OffAmazonPayments_Sandbox/2013-01-01';
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
            if ($parm === 'capture_now' || $parm === 'confirm_now' || $parm === 'inherit_shipping_address' || $parm === 'request_payment_authorization') {
                $expectedParameters[$value] = true;
                $apiCallParams[$parm] = true;
            } elseif ($parm === 'order_item_categories') {
                $apiCallParams[$parm] = array('Antiques', 'Electronics');
            } elseif ($parm === 'order_status_list') {
                $apiCallParams[$parm] = array('Open', 'Closed');
            } elseif ($parm === 'notification_configuration_list') {
                $notificationConfiguration['https://dev.null/one'] = array('ORDER_REFERENCE', 'PAYMENT_AUTHORIZE');
                $notificationConfiguration['https://dev.null/two'] = array('ALL');
                $apiCallParams[$parm] = $notificationConfiguration;
            } elseif (!isset($expectedParameters[$value])) {
                $unique_id = uniqid();
                $expectedParameters[$value] = $unique_id;
                $apiCallParams[$parm] = $unique_id;
            }
        }

        return array('expectedParameters' => $expectedParameters,
                     'apiCallParams'      => $apiCallParams);
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
        $reflectionClass = new \ReflectionClass("AmazonPay\Client");
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        $expectedStringParams = $reflectionMethod->invoke($client, $parameters);
        return $expectedStringParams;
    }
}
