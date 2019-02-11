# Amazon Pay SDK (PHP)
Amazon Pay API Integration

## Requirements

* Amazon Pay account:
 * [US - Registration](https://pay.amazon.com/us/signup)
 * [UK - Registration](https://pay.amazon.com/uk/signup)
 * [DE - Registration](https://pay.amazon.com/de/signup)
 * [JP - Registration](https://pay.amazon.com/jp/contactsales)

* PHP 5.5 or higher
* Curl 7.18 or higher

Support for PHP 5.3 and 5.4 is being deprecated. The SDK will work in these older environments, but future versions may not. We encourage merchants to move to a newer version of PHP at their earliest convenience.

## Documentation

Integration steps can be found below:
 * [US](https://pay.amazon.com/us/developer/documentation)
 * [UK](https://pay.amazon.com/uk/developer/documentation)
 * [DE](https://pay.amazon.com/de/developer/documentation)
 * [FR](https://pay.amazon.com/fr/developer/documentation)
 * [IT](https://pay.amazon.com/it/developer/documentation)
 * [ES](https://pay.amazon.com/es/developer/documentation)
 * [JP](https://pay.amazon.com/jp/developer/documentation)

## Sample

* View the [Amazon Pay SDK samples](https://amzn.github.io/amazon-pay-sdk-samples/)

## Quick Start

The client takes in parameters in the following format:

1. Associative array
2. Path to the JSON file containing configuration information.

## Installing using Composer
```
composer require amzn/amazon-pay-sdk-php
```
## Directory Tree
```
.
├── composer.json - Configuration for composer
├── LICENSE.txt
├── NOTICE.txt
├── AmazonPay
│   ├── Client.php - Main class with the API calls
│   ├── ClientInterface.php - Shows the public function definitions in Client.php
│   ├── HttpCurl.php -  Client class uses this file to execute the GET/POST
│   ├── HttpCurlInterface.php - Shows the public function definitions in the HttpCurl.php
│   ├── IpnHandler.php - Class handles verification of the IPN
│   ├── IpnHandlerInterface.php - Shows the public function definitions in the IpnHandler.php
│   ├── Regions.php -  Defines the regions that is supported
│   ├── ResponseParser.php -  Parses the API call response
│   └── ResponseInterface.php - Shows the public function definitions in the ResponseParser.php
├── README.md
└── UnitTests
    ├── ClientTest.php
    ├── config.json
    ├── coverage.txt
    ├── IpnHandlerTest.php
    └── Signature.php
```
## Parameters List

#### Mandatory Parameters
| Parameter    | variable name | Values          				|
|--------------|---------------|------------------------------------------------|
| Merchant Id  | `merchant_id` | Default : `null`				|
| Access Key   | `access_key`  | Default : `null`				|
| Secret Key   | `secret_key`  | Default : `null`				|
| Region       | `region`      | Default : `null`<br>Other: `us`,`de`,`uk`,`jp`	|

#### Optional Parameters
| Parameter           | Variable name         | Values                                      	   |
|---------------------|-----------------------|----------------------------------------------------|
| Currency Code       | `currency_code`       | Default : `null`<br>Other: `USD`,`EUR`,`GBP`,`JPY` |
| Environment         | `sandbox`             | Default : `false`<br>Other: `true`	    	   |
| Platform ID         | `platform_id`         | Default : `null` 			    	   |
| CA Bundle File      | `cabundle_file`       | Default : `null`			    	   |
| Application Name    | `application_name`    | Default : `null`			    	   |
| Application Version | `application_version` | Default : `null`			    	   |
| Proxy Host          | `proxy_host`          | Default : `null`			    	   |
| Proxy Port          | `proxy_port`          | Default : `-1`  			    	   |
| Proxy Username      | `proxy_username`      | Default : `null`			    	   |
| Proxy Password      | `proxy_password`      | Default : `null`			    	   |
| LWA Client ID       | `client_id`           | Default : `null`			    	   |
| Handle Throttle     | `handle_throttle`     | Default : `true`<br>Other: `false`	    	   |

## Setting Configuration

Your Amazon Pay keys are available in your Seller Central account

Setting configuration while instantiating the Client object
```php
<?php
namespace AmazonPay;

require_once 'Client.php';
// or, instead of using require_once, you can use the phar file instead
// include 'amazon-pay.phar';

// PHP Associative array
$config = array(
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'access_key'  => 'YOUR_ACCESS_KEY',
    'secret_key'  => 'YOUR_SECRET_KEY',
    'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
    'region'      => 'REGION');

// or, instead of setting the array in the code, you can
// initialze the Client by specifying a JSON file
// $config = 'PATH_TO_JSON_FILE';

// Instantiate the client class with the config type
$client = new Client($config);
```
### Testing in Sandbox Mode

The sandbox parameter is defaulted to false if not specified:
```php
<?php
namespace AmazonPay;

$config = array(
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'access_key'  => 'YOUR_ACCESS_KEY',
    'secret_key'  => 'YOUR_SECRET_KEY',
    'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
    'region'      => 'REGION',
    'sandbox'     => true);

$client = new Client($config);

// Also you can set the sandbox variable in the config() array of the Client class by

$client->setSandbox(true);
```
### Setting Proxy values
Proxy parameters can be set after Instantiating the Client Object with the following setter
```php
$proxy =  array();
$proxy['proxy_user_host'] // Hostname for the proxy
$proxy['proxy_user_port'] // Hostname for the proxy
$proxy['proxy_user_name'] // If your proxy requires a username
$proxy['proxy_user_password'] // If your proxy requires a password

$client->setProxy($proxy);
```

### Making an API Call

Below is an example on how to make the GetOrderReferenceDetails API call:

```php
<?php
namespace AmazonPay;

$requestParameters = array();

// AMAZON_ORDER_REFERENCE_ID is obtained from the Amazon Pay Address/Wallet widgets
// ACCESS_TOKEN is obtained from the GET parameter from the URL.

// Required Parameter
$requestParameters['amazon_order_reference_id'] = 'AMAZON_ORDER_REFERENCE_ID';

// Optional Parameter
$requestParameters['address_consent_token']  = 'ACCESS_TOKEN';
$requestParameters['mws_auth_token']         = 'MWS_AUTH_TOKEN';

$response = $client->getOrderReferenceDetails($requestParameters);

```
See the [API Response](https://github.com/amzn/amazon-pay-sdk-php#api-response) section for information on parsing the API response.

Below is an example on how to make the GetMerchantAccountStatus API call:

```php

$requestParameters = array();

// Optional Parameter
$requestParameters['mws_auth_token']         = 'MWS_AUTH_TOKEN';

$response = $client->getMerchantAccountStatus($requestParameters);
echo $response->toXml() . "\n";

// Sample Response
<GetMerchantAccountStatusResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
  <GetMerchantAccountStatusResult>
    <AccountStatus>ACTIVE</AccountStatus>
  </GetMerchantAccountStatusResult>
  <ResponseMetadata>
    <RequestId>b0a141f7-712a-4830-8014-2aa0c446b04e</RequestId>
  </ResponseMetadata>
</GetMerchantAccountStatusResponse>


```
See the [API Response](https://github.com/amzn/amazon-pay-sdk-php#api-response) section for information on parsing the API response.

Below is an example on how to make the ListOrderReference API call:

```php

$requestParameters = array();

// Required Parameter
$configArray['query_id']             = 'SELLER_ORDER_ID';
$configArray['query_id_type']        = 'SellerOrderId';

// Optional Parameter
$requestParameters['mws_auth_token'] = 'MWS_AUTH_TOKEN';
$configArray['page_size']            = "1";

$response = $client->listOrderReference($requestParameters);
echo $response->toXml() . "\n";

// Sample Response
<ListOrderReferenceResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
  <ListOrderReferenceResult>
    <OrderReferenceList>
      <OrderReference>
        <ReleaseEnvironment>Sandbox</ReleaseEnvironment>
        <OrderReferenceStatus>
          <LastUpdateTimestamp>2018-08-06T22:45:37.314Z</LastUpdateTimestamp>
          <State>Open</State>
        </OrderReferenceStatus>
        <AmazonOrderReferenceId>S01-6649662-0708590</AmazonOrderReferenceId>
        <CreationTimestamp>2018-08-06T22:45:28.203Z</CreationTimestamp>
        <SellerOrderAttributes>
          <StoreName>PHP SDK Test goGetOrderReferenceDetails</StoreName>
          <CustomInformation>PHP SDK Custom Information Testing</CustomInformation>
          <SellerOrderId>PHP SDK ID# 12345</SellerOrderId>
        </SellerOrderAttributes>
        <OrderTotal>
          <CurrencyCode>USD</CurrencyCode>
          <Amount>0.01</Amount>
        </OrderTotal>
      </OrderReference>
    </OrderReferenceList>
    <NextPageToken>eyJuZXh0UGFn...=</NextPageToken>
  </ListOrderReferenceResult>
  <ResponseMetadata>
    <RequestId>5749768d-307b-493b-90b0-8b5b9f2ea436</RequestId>
  </ResponseMetadata>
</ListOrderReferenceResponse>

```
See the [API Response](https://github.com/amzn/amazon-pay-sdk-php#api-response) section for information on parsing the API response.

Below is an example on how to make the ListOrderReferenceByNextToken API call:

```php

$requestParameters = array();

// Required Parameter
$configArray['next_page_token']            = "NEXT_PAGE_TOKEN";

$response = $client->listOrderReferenceByNextToken($requestParameters);
echo $response->toXml() . "\n";

// Sample Response
<ListOrderReferenceByNextTokenResponse xmlns="http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01">
  <ListOrderReferenceByNextTokenResult>
    <OrderReferenceList>
      <OrderReference>
        <ReleaseEnvironment>Sandbox</ReleaseEnvironment>
        <OrderReferenceStatus>
          <LastUpdateTimestamp>2018-08-06T22:42:50.191Z</LastUpdateTimestamp>
          <State>Open</State>
        </OrderReferenceStatus>
        <AmazonOrderReferenceId>S01-1662310-7599388</AmazonOrderReferenceId>
        <CreationTimestamp>2018-08-06T22:42:35.904Z</CreationTimestamp>
        <SellerOrderAttributes>
          <StoreName>PHP SDK Test goGetOrderReferenceDetails</StoreName>
          <CustomInformation>PHP SDK Custom Information Testing</CustomInformation>
          <SellerOrderId>PHP SDK ID# 12345</SellerOrderId>
        </SellerOrderAttributes>
        <OrderTotal>
          <CurrencyCode>USD</CurrencyCode>
          <Amount>0.01</Amount>
        </OrderTotal>
      </OrderReference>
    </OrderReferenceList>
    <NextPageToken>eyJuZXh0UGFnZVRva2VuIjoiQUFBQUFBQUFBQ...</NextPageToken>
  </ListOrderReferenceByNextTokenResult>
  <ResponseMetadata>
    <RequestId>8e06c852-4072-4cfb-99a3-060ec1ef7be8</RequestId>
  </ResponseMetadata>
</ListOrderReferenceByNextTokenResponse>


```
See the [API Response](https://github.com/amzn/amazon-pay-sdk-php#api-response) section for information on parsing the API response.


### IPN Handling

1. To receive IPN's successfully you will need an valid SSL on your domain.
2. You can set up your Notification endpoints in Seller Central by accessing the Integration Settings page in the Settings tab.
3. IpnHandler.php class handles verification of the source and the data of the IPN

Add the below code into any file and set the URL to the file location in Merchant/Integrator URL by accessing Integration Settings page in the Settings tab.

```php
<?php
namespace AmazonPay;

require_once 'IpnHandler.php';

// Get the IPN headers and Message body
$headers = getallheaders();
$body = file_get_contents('php://input');

// Create an object($ipnHandler) of the IpnHandler class
$ipnHandler = new IpnHandler($headers, $body);

```
See the [IPN Response](https://github.com/amzn/amazon-pay-sdk-php#ipn-response) section for information on parsing the IPN response.

### Convenience Methods

#### Charge Method

The charge method combines the following API calls:

**Standard Payments / Recurring Payments**

1. SetOrderReferenceDetails / SetBillingAgreementDetails
2. ConfirmOrderReference / ConfirmBillingAgreement
3. Authorize / AuthorizeOnBillingAgreement

For **Standard payments** the first `charge` call will make the SetOrderReferenceDetails, ConfirmOrderReference, Authorize API calls.
Subsequent call to `charge` method for the same Order Reference ID will make the call only to Authorize.

For **Recurring payments** the first `charge` call will make the SetBillingAgreementDetails, ConfirmBillingAgreement, AuthorizeOnBillingAgreement API calls.
Subsequent call to `charge` method for the same Billing Agreement ID will make the call only to AuthorizeOnBillingAgreement.

> **Capture Now** can be set to `true` for digital goods . For Physical goods it's highly recommended to set the Capture Now to `false`
and the amount captured by making the `capture` API call after the shipment is complete.


| Parameter                  | Variable Name                | Mandatory | Values                                                                                              	    |
|----------------------------|------------------------------|-----------|-----------------------------------------------------------------------------------------------------------|
| Amazon Reference ID 	     | `amazon_reference_id` 	    | yes       | OrderReference ID (`starts with P01 or S01`) or <br>Billing Agreement ID (`starts with B01 or C01`)       |
| Amazon OrderReference ID   | `amazon_order_reference_id`  | no        | OrderReference ID (`starts with P01 or S01`) if no Amazon Reference ID is provided                        |
| Amazon Billing Agreement ID| `amazon_billing_agreement_id`| no        | Billing Agreement ID (`starts with B01 or C01`) if no Amazon Reference ID is provided                     |
| Merchant ID         	     | `merchant_id`         	    | no        | Value taken from config array in Client.php                                                               |
| Charge Amount       	     | `charge_amount`       	    | yes       | Amount that needs to be captured.<br>Maps to API call variables `amount` , `authorization_amount`         |
| Currency code       	     | `currency_code`       	    | no        | If no value is provided, value is taken from the config array in Client.php      		            |
| Authorization Reference ID | `authorization_reference_id` | yes       | Unique string to be passed									            |
| Transaction Timeout 	     | `transaction_timeout`        | no        | Timeout for Authorization - Defaults to 1440 minutes						            |
| Capture Now	             | `capture_now`                | no        | Will capture the payment automatically when set to `true`. Defaults to `false`						                                            |
| Charge Note         	     | `charge_note`         	    | no        | Note that is sent to the buyer. <br>Maps to API call variables `seller_note` , `seller_authorization_note`|
| Charge Order ID     	     | `charge_order_id`     	    | no        | Custom order ID provided <br>Maps to API call variables `seller_order_id` , `seller_billing_agreement_id` |
| Store Name          	     | `store_name`          	    | no        | Name of the store                                                                                         |
| Platform ID         	     | `platform_id`         	    | no        | Platform ID of the Solution provider                                                                      |
| Custom Information  	     | `custom_information`  	    | no        | Any custom string                                                                                         |
| MWS Auth Token      	     | `mws_auth_token`      	    | no        | MWS Auth Token required if API call is made on behalf of the seller                                       |

```php
// Create an array that will contain the parameters for the charge API call
$requestParameters = array();

// Adding the parameters values to the respective keys in the array
$requestParameters['amazon_reference_id'] = 'AMAZON_REFERENCE_ID';

// Or
// If $requestParameters['amazon_reference_id'] is not provided,
// either one of the following ID input is needed
$requestParameters['amazon_order_reference_id'] = 'AMAZON_ORDER_REFERENCE_ID';
$requestParameters['amazon_billing_agreement_id'] = 'AMAZON_BILLING_AGREEMENT_ID';

$requestParameters['seller_id'] = null;
$requestParameters['charge_amount'] = '100.50';
$requestParameters['currency_code'] = 'USD';
$requestParameters['authorization_reference_id'] = 'UNIQUE STRING';
$requestParameters['transaction_timeout'] = 0;
$requestParameters['capture_now'] = false; //true for Digital goods
$requestParameters['charge_note'] = 'Example item note';
$requestParameters['charge_order_id'] = '1234-Example-Order';
$requestParameters['store_name'] = 'Example Store';
$requestParameters['platform_Id'] = null;
$requestParameters['custom_information'] = 'Any_Custom_String';
$requestParameters['mws_auth_token'] = null;

// Get the Authorization response from the charge method
$response = $client->charge($requestParameters);
```
See the [API Response](https://github.com/amzn/amazon-pay-sdk-php#api-response) section for information on parsing the API response.

#### Obtain profile information (getUserInfo method)
1. obtains the user's profile information from Amazon using the access token returned by the Button widget.
2. An access token is granted by the authorization server when a user logs in to a site.
3. An access token is specific to a client, a user, and an access scope. A client must use an access token to retrieve customer profile data.

| Parameter           | Variable Name         | Mandatory | Values                                                                       	     |
|---------------------|-----------------------|-----------|------------------------------------------------------------------------------------------|
| Access Token        | `access_token`        | yes       | Retrieved as GET parameter from the URL                                      	     |
| Region              | `region`              | yes       | Default :`null` <br>Other:`us`,`de`,`uk`,`jp`<br>Value is set in config['region'] array |
| LWA Client ID       | `client_id`           | yes       | Default: null<br>Value should be set in config array                        	     |

```php
<?php namespace AmazonPay;

// config array parameters that need to be instantiated
$config = array(
    'client_id' => 'YOUR_LWA_CLIENT_ID',
    'region'    => 'REGION');

$client = new Client($config);

// Client ID can also be set using the setter function setClientId($client_id)
$client->setClientId(‘YOUR_LWA_CLIENT_ID’);

// Get the Access Token from the URL
$access_token = 'ACCESS_TOKEN';
// Calling the function getUserInfo with the access token parameter returns object
$userInfo = $client->getUserInfo($access_token);

// Buyer name
$userInfo['name'];
// Buyer Email
$userInfo['email'];
// Buyer User Id
$userInfo['user_id'];
```
### Response Parsing

Responses are provided in 3 formats

1. XML/Raw response
2. Associative array
3. JSON format

#### API Response
```php
// Returns an object($response) of the class ResponseParser.php
$response = $client->getOrderReferenceDetails($requestParameters);

// XML response
$response->toXml();

// Associative array response
$response->toArray();

// JSON response
$response->toJson();
```

#### IPN Response
```php
$ipnHandler = new IpnHandler($headers, $body);

// Raw message response
$ipnHandler->returnMessage();

// Associative array response
$ipnHandler->toArray();

// JSON response
$ipnHandler->toJson();
```

### Logging

SDK logging of sanitized requests and responses can work with any PSR-3 compliant logger such as Monolog.

#### API Response
```php
namespace AmazonPay;
require 'vendor/autoload.php';
include 'amazon-pay.phar';
 
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('America/Los_Angeles');
$log = new Logger('TestSDK');

$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$client = new Client('us.config');
$client->setLogger($log);

$response = $client->getServiceStatus();
```
