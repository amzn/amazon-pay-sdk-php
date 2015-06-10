<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */


/**
 *  @see OffAmazonPaymentsService_Model
 */
require_once 'OffAmazonPaymentsService/Model.php';



/**
 * OffAmazonPaymentsService_Model_GetCaptureDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonCaptureId: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_GetCaptureDetailsRequest extends OffAmazonPaymentsService_Model
{
    
    /**
     * Construct new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonCaptureId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array(
            'SellerId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'AmazonCaptureId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'MWSAuthToken' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }
    
    /**
     * Gets the value of the SellerId property.
     * 
     * @return string SellerId
     */
    public function getSellerId()
    {
        return $this->_fields['SellerId']['FieldValue'];
    }
    
    /**
     * Sets the value of the SellerId property.
     * 
     * @param string SellerId
     * @return this instance
     */
    public function setSellerId($value)
    {
        $this->_fields['SellerId']['FieldValue'] = $value;
        return $this;
    }
    
    /**
     * Sets the value of the SellerId and returns this instance
     * 
     * @param string $value SellerId
     * @return OffAmazonPaymentsService_Model_GetCaptureDetailsRequest instance
     */
    public function withSellerId($value)
    {
        $this->setSellerId($value);
        return $this;
    }
    
    
    /**
     * Checks if SellerId is set
     * 
     * @return bool true if SellerId  is set
     */
    public function isSetSellerId()
    {
        return !is_null($this->_fields['SellerId']['FieldValue']);
    }
    
    /**
     * Gets the value of the AmazonCaptureId property.
     * 
     * @return string AmazonCaptureId
     */
    public function getAmazonCaptureId()
    {
        return $this->_fields['AmazonCaptureId']['FieldValue'];
    }
    
    /**
     * Sets the value of the AmazonCaptureId property.
     * 
     * @param string AmazonCaptureId
     * @return this instance
     */
    public function setAmazonCaptureId($value)
    {
        $this->_fields['AmazonCaptureId']['FieldValue'] = $value;
        return $this;
    }
    
    /**
     * Sets the value of the AmazonCaptureId and returns this instance
     * 
     * @param string $value AmazonCaptureId
     * @return OffAmazonPaymentsService_Model_GetCaptureDetailsRequest instance
     */
    public function withAmazonCaptureId($value)
    {
        $this->setAmazonCaptureId($value);
        return $this;
    }
    
    
    /**
     * Checks if AmazonCaptureId is set
     * 
     * @return bool true if AmazonCaptureId  is set
     */
    public function isSetAmazonCaptureId()
    {
        return !is_null($this->_fields['AmazonCaptureId']['FieldValue']);
    }
    
    /**
     * Gets the value of the MWSAuthToken property.
     *
     * @return string MWSAuthToken
     */
    public function getMWSAuthToken()
    {
        return $this->_fields['MWSAuthToken']['FieldValue'];
    }
    
    /**
     * Sets the value of the MWSAuthToken and returns this instance
     *
     * @param string $value MWSAuthToken
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest instance
     */
    public function setMWSAuthToken($value)
    {
        $this->_fields['MWSAuthToken']['FieldValue'] = $value;
        return $this;
    }
    
    
    /**
     * Checks if MWSAuthToken is set
     *
     * @return bool true if MWSAuthToken is set
     */
    public function isSetMWSAuthToken()
    {
        return !is_null($this->_fields['MWSAuthToken']['FieldValue']);
    }
}