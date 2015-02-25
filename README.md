<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> a8d53e9... added latest PHP SDK
# Login and Pay with Amazon PHP SDK
Login and Pay with Amazon API Integration

## Requirements

* Login and Pay With Amazon account - [Register here](https://payments.amazon.com/signup)
* PHP 5.3 or higher
* Curl 7.18 or higher

## Documentation

* The Integration steps can be found [here](https://payments.amazon.com/documentation)

## Sample

* View the sample integration demo [here](https://amzn.github.io/login-and-pay-with-amazon-sdk-samples/)

## Quick Start

Instantiating the client:
Client Takes in parameters in the following format

1. Associative array
2. Path to the JSON file containing configuration information.

## Installing using Composer
```
composer create-project amzn/login-and-pay-with-amazon-sdk-php --prefer-dist
```
## Directory Tree
```
.
├── composer.json - Configuration for composer
├── LICENSE.txt
├── NOTICE.txt
├── PayWithAmazon
│   ├── Client.php - Main class with the API calls
│   ├── HttpCurl.php -  Client class uses this file to execute the GET/POST
│   ├── Interface.php - Shows the public function definitions in the Client.php
│   ├── IpnHandler.php - Class handles verification of the IPN
│   ├── Regions.php -  Defines the regions that is supported
│   └── ResponseParser.php -  Parses the API call response
├── README.md
└── UnitTests
    ├── ClientTest.php
    ├── config.json
    ├── coverage.txt
    ├── IpnHandlerTest.php
    └── Signature.php
```
##Parameters List

####Mandatory Parameters
| Parameter    | variable name | Values          				|
|--------------|---------------|------------------------------------------------|
| Merchant Id  | `merchant_id` | Default : `null`				|
| Access Key   | `access_key`  | Default : `null`				|
| Secret Key   | `secret_key`  | Default : `null`				|
| Region       | `region`      | Default : `null`<br>Other: `us`,`de`,`uk`,`jp`	|

####Optional Parameters
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

Setting configuration while instantiating the Client object
```php
<?php
namespace PayWithAmazon;

require_once 'Client.php';
// Your Login and Pay with Amazon keys are available in your Seller Central account

// PHP Associative array
$config = array('merchant_id' => 'YOUR_MERCHANT_ID',
                'access_key'  => 'YOUR_ACCESS_KEY',
                'secret_key'  => 'YOUR_SECRET_KEY',
                'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'region'      => 'REGION');

// JSON file path
$config = 'PATH_TO_JSON_FILE';

// Instantiate the client class with the config type
$client = new Client($config);
```
### Testing in Sandbox Mode

The sandbox parameter is defaulted to false if not specified:
```php
<?php
namespace PayWithAmazon;

$config = array('merchant_id'   => 'YOUR_MERCHANT_ID',
                'access_key'    => 'YOUR_ACCESS_KEY',
                'secret_key'    => 'YOUR_SECRET_KEY',
                'client_id'     => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'region'     	=> 'REGION',
                'sandbox'       => true );

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
namespace PayWithAmazon;

$requestParameters = array();

// AMAZON_ORDER_REFERENCE_ID is obtained from the Pay with Amazon Address/Wallet widgets
// ACCESS_TOKEN is obtained from the GET parameter from the URL.

// Required Parameter
$requestParameters['amazon_order_reference_id'] = 'AMAZON_ORDER_REFERENCE_ID';

// Optional Parameter
$requestParameters['address_consent_token']     = 'ACCESS_TOKEN';
$requestParameters['mws_auth_token']            = 'MWS_AUTH_TOKEN';

$response = $client->getOrderReferenceDetails($requestParameters);

```
See the [API Response](https://github.com/amzn/login-and-pay-with-amazon-sdk-php#api-response) section for information on parsing the API response.

### IPN Handling

1. To receive IPN's successfully you will need an valid SSL on your domain.
2. You can set up your Notification endpoints in Seller Central by accessing the Integration Settings page in the Settings tab.
3. IpnHandler.php class handles verification of the source and the data of the IPN

Add the below code into any file and set the URL to the file location in Merchant/Integrator URL by accessing Integration Settings page in the Settings tab.

```php
<?php
namespace PayWithAmazon;

require_once 'IpnHandler.php';

// Get the IPN headers and Message body
$headers    = getallheaders();
$body       = file_get_contents('php://input');

// Create an object($ipnHandler) of the IpnHandler class
$ipnHandler = new IpnHandler($headers, $body);

```
See the [IPN Response](https://github.com/amzn/login-and-pay-with-amazon-sdk-php#ipn-response) section for information on parsing the IPN response.

### Convenience Methods

#####Charge Method

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
$requestParameters['amazon_order_reference_id']   = 'AMAZON_ORDER_REFERENCE_ID';
$requestParameters['amazon_billing_agreement_id'] = 'AMAZON_BILLING_AGREEMENT_ID';

$requestParameters['seller_id'] = null;
$requestParameters['charge_amount'] = '100.50';
$requestParameters['currency_code'] = 'USD';
$requestParameters['authorization_reference_id'] = 'UNIQUE STRING';
$requestParameters['transaction_timeout'] = 0;
$requestParameters['capture_now'] = false; //`true` for Digital goods
$requestParameters['charge_note'] = 'Example item note';
$requestParameters['charge_order_id'] = '1234-Example-Order';
$requestParameters['store_name'] = 'Example Store';
$requestParameters['platform_Id'] = null;
$requestParameters['custom_information'] = 'Any_Custom_String';
$requestParameters['mws_auth_token'] = null;

// Get the Authorization response from the charge method
$response = $client->charge($requestParameters);
```
See the [API Response](https://github.com/amzn/login-and-pay-with-amazon-sdk-php#api-response) section for information on parsing the API response.

#####Obtain profile information (getUserInfo method)
1. obtains the user's profile information from Amazon using the access token returned by the Button widget.
2. An access token is granted by the authorization server when a user logs in to a site.
3. An access token is specific to a client, a user, and an access scope. A client must use an access token to retrieve customer profile data.

| Parameter           | Variable Name         | Mandatory | Values                                                                       	     |
|---------------------|-----------------------|-----------|------------------------------------------------------------------------------------------|
| Access Token        | `access_token`        | yes       | Retrieved as GET parameter from the URL                                      	     |
| Region              | `region`              | yes       | Default :`null` <br>Other:`us`,`de`,`uk`,`jp`<br>Value is set in config['region'] array |
| LWA Client ID       | `client_id`           | yes       | Default: null<br>Value should be set in config array                        	     |

```php
<?php namespace PayWithAmazon;

// config array parameters that need to be instantiated
$config = array('client_id' => 'YOUR_LWA_CLIENT_ID',
                'region'    => 'REGION' );

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

#####API Response
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

#####IPN Response
```php
$ipnHandler = new IpnHandler($headers, $body);

// Raw message response
$ipnHandler->returnMessage();

// Associative array response
$ipnHandler->toArray();

// JSON response
$ipnHandler->toJson();
<<<<<<< HEAD
<<<<<<< HEAD
```
=======
login-and-pay-with-amazon-sdk-php
=================================
>>>>>>> 73e09fb... Initial commit
=======
******************************************************************************
Login and Pay with Amazon PHP Library
Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
Licensed under the Apache License, Version 2.0 (the "License"); 
******************************************************************************
  
******************************************************************************
  INTRODUCTION
******************************************************************************
 
 Please understand that by using the Login and Pay with Amazon sample code, 
 you are agreeing to understand and abide by the terms of the license, 
 as written in NOTICE.txt & LICENSE.txt accompanying this archive. 
 This sample code has been tested with PHP 5.3.8 and Curl 7.18.1
  
******************************************************************************
  INCLUDED FILES
******************************************************************************
 
 * src/                          * PHP files required to execute the code
 * LICENSE.txt                   * The Apache License this code is licensed under
 * NOTICE.txt                    * Notice file.
 * README.txt                    * This file.
 * CHANGES.txt                   * List of changes to the SDK.

******************************************************************************
  PREREQUISITES
******************************************************************************
In US, if you have registered with Amazon Payments API Integration prior to
October 7th, 2013, you will need to register with Login with Amazon (LWA) and
get a Login with Amazon (LWA) Client ID.  To register with LWA visit
https://login.amazon.com/ and click on "Sign Up" button. 
   
In EU, if you register with Amazon Payments API Integration prior to
September 9th, 2014, you will need to register with Login with Amazon (LWA) and
get a Login with Amazon (LWA) Client ID. To register with LWA contact Amazon 
Payments seller support through Seller Central and request an LwA registration. 


Once registered for LWA to get your LWA Client ID, go to Seller Central, select
the ???Login with Amazon??? marketplace on the top right switcher, click on "Register 
Your Application" button on LWA Seller Central page. For additional information,
please see the following step by step guide to get your Login with Amazon Client 
ID: https://amazonpayments.s3.amazonaws.com/documents/Get_Your_Login_with_Amazon
_Client_ID.pdf

******************************************************************************
  USAGE INSTRUCTIONS
******************************************************************************
Note: The following steps are for a UNIX based operating environment (and can
be easily modified to suite other operating systems)

This SDK includes two sets of samples - a command line based example that
requires a minimal setup in order to run, and a webserver based sample that
demonstrates notification processing.

To run the command line based examples:

  (1) Open the src/OffAmazonPaymentsService/OffAmazonPaymentsService.config
  .inc.php file and fill out the merchant Id, access key and secret key fields.
  Please also fill out LWA client Id field if Login with Amazon service is
  available in your region. Also make sure that the environment and region
  keys are configured to the right values for your test.
  	    
  (2) Navigate to the src/OffAmazonPaymentsService/Samples directory, and run
  the selected PHP sample in the console. An Amazon Order Reference Id is
  required for the examples. If Automatic Payments service is available in your
  region, an Amazon Billing Agreement Id is required for the example
  AutomaticPaymentsSimpleCheckoutExampleCLI.php

    (a) An Amazon Order Reference Id/Amazon Billing Agreement Id can be
    generated by using the OffAmazonPayments wallet widget. There are sample
    html pages titled signin.php, address.php and wallet.php that contain
    widgets ready to host on a php webserver. Please see 
    src/OffAmazonPaymentsNotifications/Samples directory for the sample pages.

    (b) For the RefundExample.php sample, you will also need to provide the
    Amazon Capture Id as a second argument which can be obtained from an earlier
    service call.

  (3) The result of the operation can be viewed by looking at the console output
  from the script.

To run the notification based examples:

  (1) Open the src/OffAmazonPaymentsService/OffAmazonPaymentsService.config
  .inc.php file and fill out the merchant Id, access key and secret key fields.
  Please also fill out LWA client Id field if Login with Amazon service is
  available in your region. Also make sure that the environment and region
  keys are configured to the right values for your test.

  (2) Open the src/OffAmazonPaymentsNotifciations/Samples/OffAmazonPaymentsNotifications
  .config.inc.php file and fill out a directory to log notification information to. 
  This folder must be a directory to which the webserver user has write permissions.

  (3) Deploy the SDK src folder to your webserver environment, and modify your
  webserver php include path to include this src directory in your default
  include path.

  (4) Login to seller central and setup the Instant Notification merchant URL on
  the Integration Settings page.  For the samples, the notification url will
  be http://<YOURHOSTNAME>(optional /<DIRECTORY>)/IpnHandler.php and this needs
  to be accessible to the internet for notification delivery to function.

  (5) In your browser, navigate to the Samples.html file in the
  src/OffAmazonPaymentsNotifications/Samples directory to execute the samples.

Using the client API:

To make service calls from your scripts:

  (1) Include the src/OffAmazonPaymentsService/Client.php file

  (2) Create a new instance of the OffAmazonPaymentsService_Client class - by
  default this will use the values defined in the
  src/OffAmazonPaymentsService/OffAmazonPaymentsService.config.inc.php file,
  which you can optionally override in the constructor call.

To handle notifications from your scripts:

  (1) Configure your webserver to accept incoming connections over HTTPS - the
  webserver based samples make use of notification processing which requires
  a HTTPS connection, and requires a server certificate that is signed by a
  trusted CA authority.  The listed of trusted CA authorities can be found at
  http://docs.aws.amazon.com/sns/latest/dg/SendMessageToHttp.https.ca.html

  (2) Include the src/OffAmazonPaymentsNotifications/Client.php file

  (3) Create a new instance of the OffAmazonPaymentsNotifications_Client class

  (4) Read in the http headers and body in your script and invoke the method on
  the client class

  (5) If the client throws an NotificationsException, return a http status code
  of 500 to indicate that the notification cannot be processed.  Otherwise
  return a http 200 code.

Note: If the status code is not returned within 15 seconds of the notification
being transmitted, it will be treated as a failed delivery.

Note that these samples use the cURL library to make calls to remote servers,
and on some may systems requires additional configuration in order to
sucessfully verify the remote server.  If you are encountering SSL connection
errors then follow these steps:    

  (1) Follow the instruction on http://curl.haxx.se/docs/sslcerts.html to obtain
  a bundled ca cert file, and ensure that it includes the signing cert for
  "VeriSign Class 3 Secure Server CA - G3"

  (2) Save the file to the following path:          
    - Windows - C:\certs\caCertFile.crt          
    - Unix - /etc/conf/certs/caCertFile.crt

  (3) Uncomment the $caBundleFile line in the
  OffAmazonPaymentsService.config.php property file and restart the service

If you save to a different path then change the value of the $caBundleFile
property to point to this directory.

Note: When deploying the SDK packages to production systems, make sure that
the Samples directories are not deployed as they are not mandatory in order to
use the client code.

******************************************************************************
  SUPPORT & PROJECT HOME
******************************************************************************
The latest documentation on the Login and Pay with Amazon can be found at the LINKS
section below:

US Amazon Seller Central: https://sellercentral.amazon.com  
EU Amazon Seller Central: https://sellercentral-europe.amazon.com

  1. Login to the site and navigate to the integration central tab to view
  available resources.

>>>>>>> 97c1ac4... -
=======
login-and-pay-with-amazon
>>>>>>> 95107e4... -
=======
```
>>>>>>> a8d53e9... added latest PHP SDK
=======
```
>>>>>>> 643790a... added a semicolon
