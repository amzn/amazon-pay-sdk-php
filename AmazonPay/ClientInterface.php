<?php
namespace AmazonPay;

/* Interface class to showcase the public API methods for Amazon Pay */

interface ClientInterface
{
    /* Setter for sandbox
     * Sets the boolean value for config['sandbox'] variable
     */
    public function setSandbox($value);


    /* Setter for config['client_id']
     * Sets the value for config['client_id'] variable
     */
    public function setClientId($value);


    /* Setter for config['app_id']
     * Sets the value for config['app_id'] variable
     */
    public function setAppId($value);


    /* Setter for Proxy
     * input $proxy [array]
     * @param $proxy['proxy_user_host'] - hostname for the proxy
     * @param $proxy['proxy_user_port'] - hostname for the proxy
     * @param $proxy['proxy_user_name'] - if your proxy required a username
     * @param $proxy['proxy_user_password'] - if your proxy required a passowrd
     */
    public function setProxy($proxy);


    /* Setter for $_mwsServiceUrl
     * Set the URL to which the post request has to be made for unit testing 
     */
    public function setMwsServiceUrl($url);


    /* Getter
     * Gets the value for the key if the key exists in config
     */
    public function __get($name);


    /* Getter for parameters string
     * Gets the value for the parameters string for unit testing
     */
    public function getParameters();


    /* GetUserInfo convenience funtion - Returns user's profile information from Amazon using the access token returned by the Button widget.
     *
     * @param $access_token [String]
     */
    public function getUserInfo($access_token);


    /* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751970
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['address_consent_token'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getOrderReferenceDetails($requestParameters = array());


    /* SetOrderReferenceDetails API call - Sets order reference details such as the order total and a description for the order.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751960
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['request_payment_authorization'] - [Boolean]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function setOrderReferenceDetails($requestParameters = array());


    /* SetOrderAttributes API call - Sets order reference attributes such as the order total and a description for the order.
     * Works same as SetOrderReferenceDetails, but includes additional PSP-related attributes and can also be called after
     * the ORO has been confirmed.  Only some values can be changed the ORO has been confirmed.  See API documentation.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['amount'] - [String]
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['request_payment_authorization'] - [Boolean]
     * @optional requestParameters['payment_service_provider_id'] - [String]
     * @optional requestParameters['payment_service_provider_order_id'] - [String]
     * @optional requestParameters['order_item_categories'] - [array()]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function setOrderAttributes($requestParameters = array());


    /* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751980
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function confirmOrderReference($requestParameters = array());


    /* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751990
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['cancelation_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function cancelOrderReference($requestParameters = array());


    /* CloseOrderReference API call - Confirms that an order reference has been fulfilled (fully or partially)
     * and that you do not expect to create any new authorizations on this order reference.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752000
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function closeOrderReference($requestParameters = array());


    /* CloseAuthorization API call - Closes an authorization.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752070
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function closeAuthorization($requestParameters = array());


    /* Authorize API call - Reserves a specified amount against the payment method(s) stored in the order reference.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752010
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['authorization_amount'] [String]
     * @param requestParameters['currency_code'] - [String]
     * @param requestParameters['authorization_reference_id'] [String]
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['provider_credit_details'] - [array (array())]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] [String] - Defaults to 1440 minutes
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function authorize($requestParameters = array());


    /* GetAuthorizationDetails API call - Returns the status of a particular authorization and the total amount captured on the authorization.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752030
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getAuthorizationDetails($requestParameters = array());


    /* Capture API call - Captures funds from an authorized payment instrument.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752040
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @param requestParameters['capture_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @param requestParameters['capture_reference_id'] - [String]
     * @optional requestParameters['provider_credit_details'] - [array (array())]
     * @optional requestParameters['seller_capture_note'] - [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function capture($requestParameters = array());


    /* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752060
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_capture_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getCaptureDetails($requestParameters = array());


    /* Refund API call - Refunds a previously captured amount.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752080
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_capture_id'] - [String]
     * @param requestParameters['refund_reference_id'] - [String]
     * @param requestParameters['refund_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['provider_credit_reversal_details'] - [array(array())]
     * @optional requestParameters['seller_refund_note'] [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function refund($requestParameters = array());


    /* GetRefundDetails API call - Returns the status of a particular refund.
     * @see https://pay.amazon.com/developer/documentation/apireference/201752100
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_refund_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getRefundDetails($requestParameters = array());


    /* GetServiceStatus API Call - Returns the operational status of the OffAmazonPayments API section
     * @see https://pay.amazon.com/developer/documentation/apireference/201752110
     *
     * The GetServiceStatus operation returns the operational status of the OffAmazonPayments API
     * section of Amazon Marketplace Web Service (Amazon MWS).
     * Status values are GREEN, GREEN_I, YELLOW, and RED.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getServiceStatus($requestParameters = array());


    /* CreateOrderReferenceForId API Call - Creates an order reference for the given object
     * @see https://pay.amazon.com/developer/documentation/apireference/201751670
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['id'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean]
     * @optional requestParameters['confirm_now'] - [Boolean]
     * @optional Amount (required when confirm_now is set to true) [String] 
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function createOrderReferenceForId($requestParameters = array());


    /* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751690
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getBillingAgreementDetails($requestParameters = array());


    /* SetBillingAgreementDetails API call - Sets Billing Agreement details such as a description of the agreement and other information about the seller.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751700
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_billing_agreement_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function setBillingAgreementDetails($requestParameters = array());


    /* ConfirmBillingAgreement API Call - Confirms that the Billing Agreement is free of constraints and all required information has been set on the Billing Agreement.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751710
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function confirmBillingAgreement($requestParameters = array());


    /* ValidateBillingAgreement API Call - Validates the status of the Billing Agreement object and the payment method associated with it.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751720
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function validateBillingAgreement($requestParameters = array());


    /* AuthorizeOnBillingAgreement API call - Reserves a specified amount against the payment method(s) stored in the Billing Agreement.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751940
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param requestParameters['authorization_reference_id'] [String]
     * @param requestParameters['authorization_amount'] [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] - Defaults to 1440 minutes
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['soft_descriptor'] - - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean] - Defaults to true
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function authorizeOnBillingAgreement($requestParameters = array());


    /* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
     * @see https://pay.amazon.com/developer/documentation/apireference/201751950
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function closeBillingAgreement($requestParameters = array());


    /* charge convenience method
     * Performs the API calls
     * 1. SetOrderReferenceDetails / SetBillingAgreementDetails
     * 2. ConfirmOrderReference / ConfirmBillingAgreement
     * 3. Authorize (with Capture) / AuthorizeOnBillingAgreeemnt (with Capture)
     *
     * @param requestParameters['merchant_id'] - [String]
     *
     * @param requestParameters['amazon_reference_id'] - [String] : Order Reference ID /Billing Agreement ID
     * If requestParameters['amazon_reference_id'] is empty then the following is required,
     * @param requestParameters['amazon_order_reference_id'] - [String] : Order Reference ID
     * or,
     * @param requestParameters['amazon_billing_agreement_id'] - [String] : Billing Agreement ID
     * 
     * @param $requestParameters['charge_amount'] - [String] : Amount value to be captured
     * @param requestParameters['currency_code'] - [String] : Currency Code for the Amount
     * @param requestParameters['authorization_reference_id'] - [String]- Any unique string that needs to be passed
     * @optional requestParameters['charge_note'] - [String] : Seller Note sent to the buyer
     * @optional requestParameters['transaction_timeout'] - [String] : Defaults to 1440 minutes
     * @optional requestParameters['charge_order_id'] - [String] : Custom Order ID provided
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function charge($requestParameters = array());


    /* GetProviderCreditDetails API Call - Get the details of the Provider Credit.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getProviderCreditDetails($requestParameters = array());


    /* GetProviderCreditReversalDetails API Call - Get details of the Provider Credit Reversal.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_reversal_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function getProviderCreditReversalDetails($requestParameters = array());


    /* ReverseProviderCredit API Call - Reverse the Provider Credit.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_id'] - [String]
     * @optional requestParameters['credit_reversal_reference_id'] - [String]
     * @param requestParameters['credit_reversal_amount'] - [String]
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['credit_reversal_note'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */
    public function reverseProviderCredit($requestParameters = array());
}
