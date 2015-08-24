<?php
namespace PayWithAmazon;

/* Interface for ResponseParser.php */

interface ResponseInterface
{
    /* Takes response from the API call */
    
    public function __construct($response = null);
    
    /* Returns the XML portion of the response */
    
    public function toXml();
    
    /* toJson  - converts XML into Json
     * @param $response [XML]
     */
    
    public function toJson();
    
    /* toArray  - converts XML into associative array
     * @param $this->_response [XML]
     */
    
    public function toArray();
    
    /* Get the status of the BillingAgreement */
    
    public function getBillingAgreementDetailsStatus($response);
}
