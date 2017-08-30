3.1.0 - May 2017
- Fix getUserInfo call (bearer token) issue impacted by 3.0.0's Curl fix
- app_id can be passed in to Client constructor as optional configuration parameter
  Needed if your Mobile SDK application needs to call the PHP SDK getUserInfo call on the backend
- access_token can be specified as a parameter to the getOrderReferenceDetails functions
  This provides access to the payment descriptor response needed for Mobile SDK implementations
  Your account must be whitelisted for Mobile SDK access to retrieve payment descriptor details
- SDK was not consistently handling boolean input parameters:
  'false' was sometimes treated as true because it was a non-empty string, 'true' was sometimes getting converted to '1' instead of true
  An Exception will be thrown if sandbox, capture_now, confirm_now, inherit_shipping_address attributes are not specified as booleans
- Fix relative paths in Psr\Log interface files

3.0.0 - March 2017
- Pay with Amazon to Amazon Pay rebranding
- PHP Archive (amazon-pay.phar) now bundled with release for convenience
- User-Agent header modified to adhere to standards
- Retry timing adjusted (1 second, 2 seconds, 7 seconds)
- Disable Curl "Expect: 100-Continue" header

2.1.0 - October 2016
- Contains PSR logging feature

2.0.4 - October 2016
- Fixing Curl implementation
- PHP 7 compatability

2.0.3 - June 2016
- Response parser fixed and added signature utility

2.0.2 - May 2016
- PSR-4 compliance changes

2.0.1 - January 2016
- Added verification for signing cert URL attribute of the IPN to ensure certificate is coming from an AWS SNS URL

2.0.0
- Rewrite of the 1.x SDK with much easier to use calling convention

1.0.16
- Added additional validation for IPN parsing.

1.0.15 
- Added Soft Decline feature for Authorization Response

1.0.14 - May 2015
- Updated sample code and web server based examples for Order Reference object and Billing Agreement object 
  containing orderLanguage parameter.
- Updated library and added orderLanguage as an additional parameter in GetOrderReferenceDetails and 
  GetBillingAgreementDetails response objects.

1.0.13 - March 2015
- Fix regression that prevented usage on PHP 5.3 and 5.4

1.0.12 - January 2015
- Fix for incorrect comparison operators in OffAmazonService/Client.php
- Fix for incorrect exception variable in OpenSslVerifySignature.php
- Add support for proxy usage in API requests
- Modified Exception.php to check for parameter existence before assigning fields
- Additional verification checks in place for IPN signature certificate validation
- Change to OffAmazonPaymentsService.config.inc.php - new mandatory property cnName
- Add support for MWSAuthToken field on all request objects

1.0.11 - September 2014
- Added 'Login & Pay with Amazon' flow for EU.
- Unified US and EU code samples

1.0.10 -May 2014
- Updated sample code and webserver based examples for using the Fast Authorization option. (No Library change needed)
- Updated library and added 
	ProviderCreditList as an additional parameter in AuthorizationRequest and CaptureRequest.
	ProviderCreditReversalList as an additional parameter in RefundRequest.
	ProviderCreditSummaryList as an additional parameter in CaptureResponse and CaptureNotification.
	ProviderCreditReversalSummaryList as an additional parameter in RefundResponse and RefundNotification.
- Updated library and added support for Solution Provider related operations (ReverseProviderCredit, GetProviderCreditDetails, GetProviderCreditReversalDetails) and notifications (ProviderCreditNotification, ProviderCreditReversalNotification)
- Added sample code and webserver based examples for ProviderCheckout, ProviderRefund and ReverseProviderCredit.
- Added support for SolutionProviderMerchantNotification.

1.0.8 - April 2014
- Updated library and added Billing Address as a whitelisted parameter in OrderReference details API
- Updated library and Added AddressVerificationCode as an additional parameter in the Authorize Notification IPN.
- Billing Address and AddressVerificationCode are available only to sellers pre-approved by Amazon. Contact Amazon Payments Support or your Account manager.

1.0.7 - March 2014
- Updated library and Added AddressVerificationCode as an additional parameter in the AuthorizationDetails object
- Added IdList as an additional parameter to the OrderReferenceDetails object.
- Added ParentDetails as an additional parameter to the OrderReferenceDetails object
- Updated sample code and webserver based examples for Billing Address use case in Billing agreement details API

1.0.6 - March 2014
- Updated library and added support for Automatic Payments related operations
- Added sample code and webserver based examples for billing agreement notifications
- Fixed typos and bugs related to undefined variable notices in library package

1.0.5 - November 2013
- Added addressConsentToken field to getOrderReferenceDetailsRequest object to
support Pay with Amazon use cases for US
- Changed sample code for US to use the new Pay with Amazon widgets
- Added new sample code for US to show usage of the address consent token
- Updated product name strings for US & EU

1.0.4 - November 2013
- Add AuthorizationBillingAddress field to AuthorizationResponse service model
object to support VAT invoicing in applicable countries (DE, UK).
- Removed EU region, added DE & UK to support future configuration options.
- Added US region in place of NA and updated sample docs to use new option - NA
is deprecated but will still function to support existing merchants.
- Modified SimpleCheckout example to show call to getAuthorizationDetails after
receiving an authorization IPN.
- Added additional property to setup cert folder so that additional certificates
can be used during SSL verification check
- Modified sample code for verifying refund is completed to accept a RefundDetails object 
in place of a GetRefundDetailsResponse object, so that it can be used from either the RefundResponse 
or getRefundDetailsResponse object references.

1.0.3 - October 2013
- Fixed issue with signature verification for windows platforms.

1.0.2 - October 2013
- Added EU endpoints to service code
- Added platformID field to setOrderReferenceDetails object

1.0.1 - May 2013
- Added payment notification model objects
- Added sample code and webserver based examples for payment notifications

1.0.0 - April 2013
- Initial release
