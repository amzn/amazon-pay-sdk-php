# Login and Pay with Amazon PHP SDK
Login and Pay with Amazon API Integration

## Requirements

* PHP 5.3 or higher
* Curl 7.8

## Quick Start

Instantiating the client:
Client Takes in parameters in the following format

1. Associative array
2. Path to the JSON file containing configuration information.

##Parameters List

####Mandatory Parameters
| Parameter  | variable name | Values          |
|----------- |---------------|-----------------|
| Merchant Id  | `merchant_id`   | Default : `null`|
| Access Key | `access_key`  | Default : `null`|
| Secret Key | `secret_key`  | Default : `null`|

####Optional Parameters
| Parameter           | Variable name         | Values                                      |
|---------------------|-----------------------|---------------------------------------------|
| Region              | `region`              | Default : `na`<br>Other: `de`,`uk`,`us`,`eu`|
| Currency Code       | `currency_code`       | Default : `USD`<br>Other: `EUR`,`GBP`,`JPY` |
| Environment         | `sandbox`             | Default : `false`<br>Other: `true`	    |
| MWS Auth token      | `mws_auth_token`      | Default : `null` 			    |
| Platform ID         | `platform_id`         | Default : `null` 			    |
| CA Bundle File      | `cabundle_file`       | Default : `null`			    |
| Application Name    | `application_name`    | Default : `null`			    |
| Application Version | `application_version` | Default : `null`			    |
| Proxy Host          | `proxy_host`          | Default : `null`			    |
| Proxy Port          | `proxy_port`          | Default : `-1`  			    |
| Proxy Username      | `proxy_username`      | Default : `null`			    |
| Proxy Password      | `proxy_password`      | Default : `null`			    |
| LWA Client ID       | `client_id`           | Default : `null`			    |
| Profile Region      | `user_profile_region` | Default : `us`<br>Other: `de`,`uk`,`jp`	    |
| Handle Throttle     | `handle_throttle`     | Default : `true`<br>Other: `false`	    |

## Setting Configuration

Setting configuration while instantiating the OffAmazonPayments_Client object
```php
require 'Client.php'
# Your Login and Pay with Amazon keys are
# available in your Seller Central account

// PHP Associative array
$config = array('merchant_id' => 'YOUR_MERCHANT_ID',
                'access_key'  => 'YOUR_ACCESS_KEY',
                'secret_key'  => 'YOUR_SECRET_KEY',
                'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID');

// JSON file path            
$config = 'PATH_TO_JSON_FILE';

//Instantiate the client class with the config type
$client = new OffAmazonPayments_Client($config);
```
### Testing in Sandbox Mode

The sandbox parameter is defaulted to false if not specified:
```php
$config = array('merchant_id'   => 'YOUR_MERCHANT_ID',
                'access_key'    => 'YOUR_ACCESS_KEY',
                'secret_key'    => 'YOUR_SECRET_KEY',
                'client_id'     => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'sandbox'       => true );

$client = new OffAmazonPayments_Client($config);

Also you can set the sandbox variable in the _config() array of the Client class by 

$client->setSandbox(true);
```
### Setting Proxy values
Proxy parameters can be set after Instantiating the Client Object with the following setter
```php
$proxy =  array();
$proxy['proxy_user_host'] // hostname for the proxy
$proxy['proxy_user_port'] // hostname for the proxy
$proxy['proxy_user_name'] // if your proxy requires a username
$proxy['proxy_user_password'] // if your proxy requires a password

$client->setProxy($proxy);
```

### Making an API Call

Below is an example on how to make the GetOrderReferenceDetails API call:

```php
$requestParameters = array();
# These values are grabbed from the Login and Pay
# with Amazon Address and Wallet widgets
$requestParameters['amazon_order_reference_id'] = 'AMAZON_ORDER_REFERENCE_ID';
$requestParameters['address_consent_token']    = 'ACCESS_TOKEN';

$response = $client->getOrderReferenceDetails($requestParameters);

```

### IPN Handling

1. To receive IPN's successfully you will need an valid SSL on your domain.
2. You can set up your Notification endpoints in Seller Central by accessing the Integration Settings page in the Settings tab.
3. IpnHandler.php class handles verifiication of the source and the data of the IPN

```php
require_once 'IpnHandler.php';

//get the IPN headers and Message body
$headers    = getallheaders();
$body       = file_get_contents('php://input');

//create an  object($ipnHandler) of the IpnHandler class 
$ipnHandler = new IpnHandler($headers, $body);

```
### Convenience Methods

#####Charge Method

Charge method combines the following API calls 

**Standard Payments / Recurring Payments**

1. SetOrderReferenceDetails / SetBillingAgreementDetails
2. ConfirmOrderReference / ConfirmBillingAgreement
3. Authorize (With capture) / AuthorizeOnBillingAgreement (With capture)

| Parameter           | Variable Name         | Mandatory | Values                                                                                              |
|---------------------|-----------------------|-----------|-----------------------------------------------------------------------------------------------------|
| Amazon Reference ID | `amazon_reference_id` | yes       | OrderReference ID (`starts with P01 or S01`) or <br>Billing Agreement ID (`starts with B01 or C01`) |
| Merchant ID         | `merchant_id`         | no        | value taken from _config array in Client.php                                                        |
| Charge Amount       | `charge_amount`       | yes       | Amount that needs to be captured.                                                                   |
| Currency code       | `currency_code`       | no        | value is taken form the _config array in Client.php                                                 |
| Charge Note         | `charge_note`         | no        | Note that is sent to the buyer                                                                      |
| Charge Order ID     | `charge_order_id`     | no        | custom order ID provided                                                                            |
| Store Name          | `store_name`          | no        | Name of the store                                                                                   |
| Platform ID         | `platform_id`         | no        | Platform ID of the Solution provider                                                                |
| Custom Information  | `custom_information`  | no        | Any custom string                                                                                   |
| MWS Auth Token      | `mws_auth_token`      | no        | MWS Auth Token required if API call is made on behalf of the seller                                                                                   |

```php
//create an array that will contain the parameters for the Charge API call
$requestParameters = array();

//Adding the parameters values to the respective keys in the array
$requestParameters['amazon_reference_id'] = 'AMAZON_REFERENCE_ID';
$requestParameters['seller_id'] = null;
$requestParameters['charge_amount'] = '100.50';
$requestParameters['currency_code'] = 'USD';
$requestParameters['charge_note'] = 'Example item note';
$requestParameters['charge_order_id'] = '1234-Example-Order';
$requestParameters['Store_Name'] = 'Example Store';
$requestParameters['Platform_Id'] = null;
$requestParameters['Custom_Information'] = "Any_Custom_String";
$requestParameters['mws_auth_token'] = null;

//get the Authorization response from the charge method
$response = $client->charge($requestParameters);
```
#####Obtain profile information (getUserInfo method)
1. obtains the user's profile information from Amazon using the access token returned by the Button widget. 
2. An access token is granted by the authorization server when a user logs in to a site. 
3. An access token is specific to a client, a user, and an access scope. A client must use an access token to retrieve customer profile data. 

| Parameter           | Variable Name         | Mandatory | Values                                                                       |
|---------------------|-----------------------|-----------|------------------------------------------------------------------------------|
| Access Token        | `access_token`        | yes       | Retrieved as GET parameter from the URL                                      |
| User Profile Region | `user_profile_region` | no        | Default :`na` <br>Other:`us`,`de`,`uk`,`jp`<br>Value is set in _config array |
| LWA Client ID       | `client_id`           | yes       | Defaulf: null<br>Value should be set in _config array                        |

```php
//create an array that will contain the parameters for the Charge API call
$config = array('merchant_id'        => 'YOUR_MERCHANT_ID',
                'access_key'         => 'YOUR_ACCESS_KEY',
                'secret_key'         => 'YOUR_SECRET_KEY',
                'client_id'          => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'user_profile_region => 'PROFILE_REGION' );

$client = new OffAmazonPayments_Client($config);

//Get the Access Token from the URL
$access_token = 'ACCESS_TOKEN';
//calling the function getUserInfo with the access token parameter returns object
$userInfoObject = $client->getUserInfo($access_token);

//Buyer name
$userInfoObject->name;
//Buyer Email
$userInfoObject->email;
//Buyer User Id
$userInfoObject->user_id;
```
### Response Parsing

Responses are provided in 3 formats

1. Raw XML response
2. Associative array
3. JSON format

#####API Response
```php
//Returns an object($response) of the class ResponseParser.php
$response = $client->getOrderReferenceDetails($requestParameters);

#XML response
$response->xmlResponse;

#Associate array response
$response->toArray();

#JSON response
$response->toJson();
```

#####IPN Response
```php
$ipnHandler = new IpnHandler($headers, $body);

#XML response
$ipnHandler->returnMessage();

#Associate array response
$ipnHandler->toArray();

#JSON response
$ipnHandler->toJson();
```