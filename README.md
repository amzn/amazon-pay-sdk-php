# Login and Pay with Amazon PHP SDK
Login and Pay with Amazon API Integration

# Install

```
gem install off_amazon_payments
```

## Requirements

* PHP 5.3 or higher

## Quick Start

Instantiating the client:
Client Takes in parameters in the following format

1. Associative array
2. JSON string
3. Path to the JSON file

##Parameters List
| _Parameter_   | _variable name_ | _mandatory_ | _values_ |
|:-------------:|:-----------:|:----------:|:----------:|
| Seller Id  | `seller_id`      | yes        |
| Access Key | `access_key`     | yes        |
| Secret Key | `secret_key`     | yes        |
| Region     | `secret_key`     | yes        |
| Currency Code | `currency_code`    | no        |
| Secret Key | secret_key     | yes        |
| Environment | `region`     | yes        |
| MWS Auth token | `mws_auth_token`     | yes        |
| Platform ID | `platform_id`     | yes        |
| CA Bundle File | `cabundle_fule`     | yes        |
| Application Name | `application_name`     | yes        |
| Application Version | `application_file`     | yes        |
| Proxy Host | `proxy_host`     | yes        |
| Proxy Port | `proxy_port`     | yes        |
| Proxy Username | `proxy_username`     | yes        |
| Proxy Password | `proxy_password`     | yes        |
| LWA Client ID | `client_id`     | yes        |
| Profile Region | `user_profile_region`     | yes        |
| Handle Throttle | `handle_throttle`     | yes        |

```php
require 'Client.php'
# Your Login and Pay with Amazon keys are
# available in your Seller Central account

## PHP Associative array
$config = array('merchant_id' => 'YOUR_MERCHANT_ID',
                'access_key'  => 'YOUR_ACCESS_KEY',
                'secret_key'  => 'YOUR_SECRET_KEY',
                'client_id'   => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID');

## JSON string input
$config = {
           "merchant_id": "YOUR_MERCHANT_ID",
           "access_key" : "YOUR_ACCESS_KEY",
           "secret_key" : "YOUR_SECRET_KEY",
           "client_id"  : "YOUR_LOGIN_WITH_AMAZON_CLIENT_ID"
           }

## JSON file path            
$config = 'PATH_TO_JSON_FILE';
```
#####Instantiate the client class with the config type 

```php
$client = new OffAmazonPayments_Client($config)
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
### Adjusting Region and Currency Code

```php
## PHP Associative array
$config = array('merchant_id'   => 'YOUR_MERCHANT_ID',
                'access_key'    => 'YOUR_ACCESS_KEY',
                'secret_key'    => 'YOUR_SECRET_KEY',
                'client_id'     => 'YOUR_LOGIN_WITH_AMAZON_CLIENT_ID',
                'region'        => 'uk',
                'currency_code' => 'GBP');
                
You can also set the region parameter with the setter funtion which can be simply accessed by
$client->region = 'uk';
```

### Making an API Call

Below is an example on how to make the GetOrderReferenceDetails API call:

```ruby
require 'off_amazon_payments'

# Your Login and Pay with Amazon keys are
# available in your Seller Central account
merchant_id = 'YOUR_MERCHANT_ID'
access_key = 'YOUR_ACCESS_KEY'
secret_key = 'YOUR_SECRET_KEY'

client = OffAmazonPayments::Client.new(
  merchant_id,
  access_key,
  secret_key,
  sandbox: true
)

# These values are grabbed from the Login and Pay
# with Amazon Address and Wallet widgets
amazon_order_reference_id = 'AMAZON_ORDER_REFERENCE_ID'
address_consent_token = 'ADDRESS_CONSENT_TOKEN'

client.get_order_reference_details(
  amazon_order_reference_id,
  address_consent_token: address_consent_token
)

```

### Response Parsing

```ruby
# These values are grabbed from the Login and Pay
# with Amazon Address and Wallet widgets
amazon_order_reference_id = 'AMAZON_ORDER_REFERENCE_ID'
address_consent_token = 'ADDRESS_CONSENT_TOKEN'

response = client.get_order_reference_details(
  amazon_order_reference_id,
  address_consent_token: address_consent_token
)

# This will return the original response body as a String
response.body

# This will return a REXML object
response.to_xml

# The 'get_element' method is an extension of the
# REXML/Document class and allows quick parsing
# of the REXML object. This will return a String value
# for the specified element.
xpath = 'XPath for the node you would like to extract'
element = 'Node/Element name you would like to extract the value from'
response.to_xml.get_element(xpath, element)

# This will return the status code of the response
response.code

# This will return true or false depending on the status code
response.success
```
