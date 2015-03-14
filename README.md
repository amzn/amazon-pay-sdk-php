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
| Parameter  | variable name | values          |
|----------- |---------------|-----------------|
| Seller Id  | `seller_id`   | Default : `null`|
| Access Key | `access_key`  | Default : `null`|
| Secret Key | `secret_key`  | Default : `null`|

####Optional Parameters
| Parameter           | variable name         | values                                      |
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

## PHP Associative array
$config = array('merchant_id' => 'YOUR_MERCHANT_ID',
                'access_key'  => 'YOUR_ACCESS_KEY',
                'secret_key'  => 'YOUR_SECRET_KEY',
                'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID');

## JSON file path            
$config = 'PATH_TO_JSON_FILE';

#####Instantiate the client class with the config type
$client = new OffAmazonPayments_Client($config);
```
### Testing in Sandbox Mode

The sandbox parameter is defaulted to false if not specified:
```php
$client = new OffAmazonPayments_Client($config)

$config = array('merchant_id'   => 'YOUR_MERCHANT_ID',
                'access_key'    => 'YOUR_ACCESS_KEY',
                'secret_key'    => 'YOUR_SECRET_KEY',
                'client_id'     => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'sandbox'       => true );

Also you can set the sandbox variable in the _config() array of the Client class by 

$client->sandbox = true;
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