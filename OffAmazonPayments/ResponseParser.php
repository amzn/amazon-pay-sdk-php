
<?php
/*******************************************************************************
 *  Copyright 2015 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
class OrderReferenceDetailsResponse
{
    public $OrderReferenceId = null;
    public $ExpirationTimestamp = null;
    public $CreationTimestamp = null;
    public $ReasonCode = null;
    public $ReasonDescription = null;
    public $Constraint = array();
    public $Description = array();
    public $HasConstraint = false;
    
    public $OrderReferenceState = null;
    public $SellerNote = null;
    public $PlatformId = null;
    
    public $ReleaseEnvironment = null;
    public $Amount = null;
    public $CurrencyCode = null;
    
    public $AuthorizationId = array();
    
    public $Phone = null;
    public $Name = null;
    public $Email = null;
    
    public $StateOrRegion = null;
    public $AddressLine1 = null;
    public $AddressLine2 = null;
    public $AddressLine3 = null;
    public $City = null;
    public $CountryCode = null;
    public $District = null;
    public $County = null;
    public $DestinationType = null;
    
    public $StoreName = null;
    public $SellerOrderId = null;
    public $CustomInformation = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    
    public $isOrderReferenceDetailsSuccess = false;
    
    
    
    public function __construct($response)
    {
        $Responsetype= array('GetORO' => 'GetOrderReferenceDetailsResult',
                             'SetORO' => 'SetOrderReferenceDetailsResult',
                             'OROIpn' => 'OrderReference');
        $increment = 0;
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response[$Responsetype['GetORO']]) ||
                  isset($response[$Responsetype['SetORO']]) ||
                  isset($response[$Responsetype['OROIpn']])) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'AmazonOrderReferenceId')
                    $this->OrderReferenceId = $value;
                    
                elseif ($key === 'CreationTimestamp')
                    $this->CreationTimestamp = $value;
                    
                elseif ($key === 'ExpirationTimestamp')
                    $this->ExpirationTimestamp = $value;
                    
                elseif ($key === 'ReasonCode')
                    $this->ReasonCode = $value;
                    
                elseif ($key === 'ReasonDescription')
                    $this->ReasonDescription = $value;
                    
                elseif ($Parentkey === 'Constraint' && $key === 'ConstraintID'){
                    $this->Constraint['ConstraintID_'. ++$increment] = $value;
                    $this->HasConstraint = true;
                }
                
                elseif ($Parentkey === 'Constraint' && $key === 'Description')
                    $this->Description['Description_'. $increment] = $value;
                
                elseif ($key === 'SellerNote')
                    $this->SellerNote = $value;
                
                elseif ($key === 'Amount')
                    $this->Amount = $value;
                
                elseif ($key === 'CurrencyCode')
                    $this->CurrencyCode = $value;
                
                 elseif ($key === 'PlatformId')
                    $this->PlatformId = $value;
               
                elseif (($Parentkey === 'member') && is_numeric($key))
                    $this->AuthorizationId['AuthorizationId_' . ++$key] = $value;
                
                elseif ($key === 'PostalCode')
                    $this->PostalCode = $value;
                
                elseif ($key === 'Name')
                    $this->Name = $value;
                
                elseif ($key === 'Email')
                    $this->Email = $value;
                
                elseif ($key === 'Phone')
                    $this->Phone = $value;
                
                elseif ($key === 'CountryCode')
                    $this->CountryCode = $value;
                
                elseif ($key === 'StateOrRegion')
                    $this->StateOrRegion = $value;
                
                elseif ($key === 'AddressLine1')
                    $this->AddressLine1 = $value;
                
                elseif ($key === 'AddressLine2')
                    $this->AddressLine2 = $value;
                
                elseif ($key === 'AddressLine3')
                    $this->AddressLine3 = $value;
                
                elseif ($key === 'City')
                    $this->City = $value;
                
                elseif ($key === 'State')
                    $this->OrderReferenceState = $value;
                
                elseif ($key === 'County')
                    $this->County = $value;
                
                elseif ($key === 'District')
                    $this->District = $value;
                
                elseif ($key === 'DestinationType')
                    $this->DestinationType = $value;
                
                elseif ($key === 'ReleaseEnvironment')
                    $this->ReleaseEnvironment = $value;
                
                elseif ($key === 'StoreName')
                    $this->StoreName = $value;
                
                elseif ($key === 'SellerOrderId') 
                    $this->SellerOrderId = $value;
                
                elseif ($key === 'CustomInformation') 
                    $this->CustomInformation = $value;
                
                $this->isOrderReferenceDetailsSuccess = true;
            }
            
        } else {
            throw new Exception("Response is Empty");
        }
    }
}

class GenericResponse
{
    
    public $RequestId = null;
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isSuccess = false;
    
    public function __construct($response)
    {
        $Responsetype = array('ConfirmORO' => 'ConfirmOrderReferenceResponse',
                              'CloseORO'   => 'CloseOrderReferenceResponse',
                              'CancelORO'  => 'CancelOrderReferenceResponse',
                              'ConfirmBA'  => 'ConfirmBillingAgreementResponse',
                              'CloseBA'    => 'CloseBillingAgreementResponse',
                              'CloseAuth'  => 'CloseAuthorizationResponse');
        $Responsetypefound = false;
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        }  elseif(isset($response['ResponseMetadata'])){
            
                foreach ($Responsetype as $key => $value) {
                    if ($response['ResponseType']['ResponseName']===$value){
                        $Responsetypefound = true;
                        
                        foreach ($iterator as $key => $value) {
                            if ($key === 'RequestId') {
                                $this->RequestId = $value;
                                $this->isSuccess = true;
                            }
                        }
                        break;
                    
                    }
                    
                }
                if(!$Responsetypefound){
                    throw new Exception("This method does not support the repsonse for the API call, please check to call the right Response Function");
                }
        }
    }
}


class AuthorizeResponse
{
    public $AuthorizationId = null;
    public $AuthorizationReferenceId = null;
    public $SellerAuthorizationNote = null;
    
    public $AuthorizationAmount = null;
    public $CapturedAmount = null;
    public $CapturedCurrencyCode = null;
    public $AuthCurrencyCode = null;
    
    public $AuthFeeAmount = null;
    public $AuthFeeCurrencyCode = null;
    public $AuthorizationState = null;
    public $CaptureId = array();
    
    public $LastUpdateTimestamp = null;
    public $ExpirationTimestamp = null;
    public $CreationTimestamp = null;
    
    public $ReasonCode = null;
    public $ReasonDescription = null;
    
    public $CaptureNow = null;
    public $SoftDescriptor = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isAuthorizeSuccess = false;
    
    
    
    public function __construct($response)
    {
        $Responsetype= array('Auth' => 'AuthorizeResult',
                             'AuthBA' => 'AuthorizeOnBillingAgreementResult',
                             'GetAuth' => 'GetAuthorizationDetailsResult',
                             'GetBAAuth' => 'GetAuthorizeOnBillingAgreementResult',
                             'AuthIpn' => 'AuthorizationDetails');
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response[$Responsetype['Auth']])||
                  isset($response[$Responsetype['AuthBA']])||
                  isset($response[$Responsetype['GetAuth']])||
                  isset($response[$Responsetype['GetBAAuth']])||
                  isset($response[$Responsetype['AuthIpn']])
                 ) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'AmazonAuthorizationId')
                    $this->AuthorizationId = $value;
                
                elseif ($key === 'ExpirationTimestamp')
                    $this->ExpirationTimestamp = $value;
                
                elseif ($key === 'AuthorizationReferenceId')
                    $this->AuthorizationReferenceId = $value;
                
                elseif ($key === 'SellerNote')
                    $this->SellerNote = $value;
                elseif ($key === 'SellerAuthorizationNote')
                    $this->SellerAuthorizationNote = $value;
                elseif ($Parentkey === 'AuthorizationAmount' && $key === 'Amount')
                    $this->AuthorizationAmount = $value;
                elseif ($Parentkey === 'AuthorizationAmount' && $key === 'CurrencyCode')
                    $this->AuthCurrencyCode = $value;
                elseif ($Parentkey === 'CapturedAmount' && $key === 'Amount')
                    $this->CapturedAmount = $value;
                elseif ($Parentkey === 'CapturedAmount' && $key === 'CurrencyCode')
                    $this->CapturedCurrencyCode = $value;
                elseif ($Parentkey === 'AuthorizationFee' && $key === 'Amount')
                    $this->AuthFeeAmount = $value;
                elseif ($Parentkey === 'AuthorizationFee' && $key === 'CurrencyCode')
                    $this->AuthFeeCurrencyCode = $value;
                elseif ($key === 'State')
                    $this->AuthorizationState = $value;
                elseif (($Parentkey === 'member') && is_numeric($key))
                    $this->CaptureId['CaptureId_' . ++$key] = $value;
                elseif ($key === 'ReasonCode')
                    $this->ReasonCode = $value;
                elseif ($key === 'ReasonDescription')
                    $this->ReasonDescription = $value;
                elseif ($key === 'CaptureNow')
                    $this->CaptureNow = $value;
                elseif ($key === 'SoftDescriptor')
                    $this->SoftDescriptor = $value;
                elseif ($key === 'LastUpdateTimestamp')
                    $this->LastUpdateTimestamp = $value;
                elseif ($key === 'ExpirationTimestamp')
                    $this->ExpirationTimestamp = $value;
                elseif ($key === 'CreationTimestamp')
                    $this->CreationTimestamp = $value;
            }
            
            $this->isAuthorizeSuccess = true;
        }
        
    }
}

class CaptureResponse {
    public $CaptureId = null;
    public $CaptureReferenceId = null;
    public $CaptureNote = null;
    
    public $CaptureAmount = null;
    public $CaptureCurrencyCode = null;
    
    public $RefundAmount = null;
    public $RefundCurrencyCode = null;
    
    public $CaptureFeeAmount = NULL;
    PUBLIC $CaptureFeeCurrencyCode = NULL;
    
    public $CaptureState = null;
    public $RefundId = array();
    
    public $LastUpdateTimestamp = null;
    public $CreationTimestamp = null;
    
    public $ReasonCode = null;
    public $ReasonDescription = null;
    public $SoftDescriptor = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isCaptureSuccess = false;
    
    
    public function __construct($response)
    {
        $Responsetype= array('Capture' => 'CaptureResult',
                             'GetCapture' => 'GetCaptureDetailsResult',
                             'CaptureIpn' => 'CaptureDetails');
    
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif(isset($response[$Responsetype['Capture']])||
                  isset($response[$Responsetype['GetCapture']])||
                  isset($response[$Responsetype['CaptureIpn']])
                ) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'AmazonCaptureId')
                    $this->CaptureId = $value;
                
                elseif ($key === 'CaptureReferenceId')
                    $this->CaptureReferenceId = $value;
                
                elseif ($key === 'SellerNote')
                    $this->SellerNote = $value;
                
                elseif ($key === 'SellerCaptureNote')
                    $this->CaptureNote = $value;
                
                elseif ($Parentkey === 'CaptureAmount' && $key === 'Amount')
                    $this->CaptureAmount = $value;
                
                elseif ($Parentkey === 'CaptureAmount' && $key === 'CurrencyCode')
                    $this->CaptureCurrencyCode = $value;
                
                elseif ($Parentkey === 'RefundedAmount' && $key === 'Amount')
                    $this->RefundAmount = $value;
                
                elseif ($Parentkey === 'RefundedAmount' && $key === 'CurrencyCode')
                    $this->RefundCurrencyCode = $value;
                
                elseif ($Parentkey === 'CaptureFee' && $key === 'Amount')
                    $this->CaptureFeeAmount = $value;
                
                elseif ($Parentkey === 'CaptureFee' && $key === 'CurrencyCode')
                    $this->CaptureFeeCurrencyCode = $value;
                
                elseif ($key === 'State')
                    $this->CaptureState = $value;
                
                elseif (($Parentkey === 'member') && is_numeric($key))
                    $this->RefundId['RefundId_' . ++$key] = $value;   
                
                elseif ($key === 'SoftDescriptor')
                    $this->SoftDescriptor = $value;
                
                elseif ($key === 'ReasonCode')
                    $this->ReasonCode = $value;
                
                elseif ($key === 'ReasonDescription')
                    $this->ReasonDescription = $value;
                
                elseif ($key === 'LastUpdateTimestamp')
                    $this->LastUpdateTimestamp = $value;
                
                elseif ($key === 'CreationTimestamp')
                    $this->CreationTimestamp = $value;
            }
            
            $this->isCaptureSuccess = true;
        }
        
    }
}

class RefundResponse{
    
    public $RefundId = null;
    public $RefundReferenceId = null;
    public $RefundNote = null;
    
    public $RefundAmount = null;
    public $RefundCurrencyCode = null;
    public $RefundType = null;
    public $CaptureFeeRefundAmount = null;
    PUBLIC $CaptureFeeRefundCurrencyCode = null;
    
    public $RefundState = null;
    
    public $LastUpdateTimestamp = null;
    public $CreationTimestamp = null;
    
    public $ReasonCode = null;
    public $ReasonDescription = null;
    public $SoftDescriptor = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isRefundSuccess = false;
    
    
    public function __construct($response)
    {
        $Responsetype= array('Refund' => 'RefundResult',
                             'GetRefund' => 'GetRefundDetailsResult',
                             'RefundIpn' => 'RefundDetails');
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response[$Responsetype['Refund']])||
                  isset($response[$Responsetype['GetRefund']])||
                  isset($response[$Responsetype['RefundIpn']])
                  ) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'AmazonRefundId')
                    $this->RefundId = $value;
                    
                elseif ($key === 'RefundReferenceId')
                    $this->RefundReferenceId = $value;
                
                elseif ($key === 'SellerNote')
                    $this->SellerNote = $value;
                
                elseif ($key === 'SellerRefundNote')
                    $this->RefundNote = $value;
                
                elseif ($key === 'RefundType')
                    $this->RefundType = $value;
                
                elseif ($Parentkey === 'RefundedAmount' && $key === 'Amount')
                    $this->RefundAmount = $value;
                
                elseif ($Parentkey === 'RefundedAmount' && $key === 'CurrencyCode')
                    $this->RefundCurrencyCode = $value;
                 
                elseif ($Parentkey === 'FeeRefunded' && $key === 'Amount')
                    $this->CaptureFeeRefundAmount = $value;
                
                elseif ($Parentkey === 'FeeRefunded' && $key === 'CurrencyCode')
                    $this->CaptureFeeRefundCurrencyCode = $value;
                
                elseif ($key === 'State')
                    $this->RefundState = $value; 
                
                elseif ($key === 'SoftDescriptor')
                    $this->SoftDescriptor = $value;
                
                elseif ($key === 'ReasonCode')
                    $this->ReasonCode = $value;
                
                elseif ($key === 'ReasonDescription')
                    $this->ReasonDescription = $value;
                
                elseif ($key === 'LastUpdateTimestamp')
                    $this->LastUpdateTimestamp = $value;
                
                elseif ($key === 'CreationTimestamp')
                    $this->CreationTimestamp = $value;
            }
            
            $this->isRefundSuccess = true;
        }
        
    }
}

class GetServiceStatusResponse
{
    
    public $ServiceStatus = null;
    public $MessageId = null;
    
    public $Message = null;
    public $Locale = null;
    public $Text = null;
    public $Timestamp = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isGetServiceSuccess = false;
    
    public function __construct($response)
    {
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response['GetServiceStatusResult'])) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'Status')
                    $this->ServiceStatus = $value;
                    
                elseif ($key === 'MessageId')
                    $this->MessageId = $value;
                    
                elseif ($key === 'Message')
                    $this->Message = $value;
                
                elseif ($key === 'Locale')
                    $this->Locale = $value;
                
                elseif ($key === 'Text')
                    $this->Text = $value;
                
                elseif ($key === 'Timestamp')
                    $this->Timestamp = $value;
            }
            
            $this->isGetServiceSuccess = true;
        }
        
    }
}

class BillingAgreementDetailsResponse
{
    //Billing Agreement general details
    public $BillingAgreementId = null;
    public $BillingAgreementState = null;
    public $ReleaseEnvironment = null;
    public $Constraint = array();
    public $Description = array();
    public $HasConstraint = false;
    
    //Billing Agreement Limits
    public $AmountLimit = null;
    public $AmountLimitCurrencyCode = null;
    public $BalanceAmount = null;
    public $BalanceAmountCurrencyCode = null;
    
    //Billing Agreement Seller details
    public $SellerNote = null;
    public $StoreName = null;
    public $SellerOrderId = null;
    public $PlatformId = null;
    public $SellerBillingAgreementId = null;
    
    //Billing Agreement Validity details
    public $TimePeriodStartDate = null;
    public $TimePeriodEndDate = null;
    public $LastUpdatedTimestamp = null;
    
    //Buyer Login Details
    public $Phone = null;
    public $Name = null;
    public $Email = null;
    
    //Buyer Address Details
    public $AddressLine1 = null;
    public $AddressLine2 = null;
    public $AddressLine3 = null;
    public $CountryCode = null;
    public $StateOrRegion = null;
    public $City = null;
    public $County= null;
    public $District= null;
    public $DestinationType = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    
    public $isBillingAgreementDetailsSuccess = false;
    
    
    
    public function __construct($response)
    {
        $Responsetype= array('GetBA' => 'GetBillingAgreementDetailsResult',
                                 'SetBA' => 'SetBillingAgreementDetailsResult');
        $increment = 0;
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response[$Responsetype['GetBA']]) || isset($response[$Responsetype['SetBA']])) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'AmazonBillingAgreementId')
                    $this->BillingAgreementId = $value;
                
                elseif ($key === 'State')
                    $this->BillingAgreementState = $value;
                
                elseif ($key === 'ReleaseEnvironment')
                    $this->ReleaseEnvironment = $value;
                
                elseif ($Parentkey === 'Constraint' && $key === 'ConstraintID'){
                    $this->Constraint['ConstraintID_'. ++$increment] = $value;
                    $this->HasConstraint = true;
                }
                
                elseif ($Parentkey === 'Constraint' && $key === 'Description')
                    $this->Description['Description_'. $increment] = $value;
                
                
                elseif (($Parentkey === 'AmountLimitPerTimePeriod') && $key === 'Amount')
                    $this->AmountLimit = $value;
                
                elseif (($Parentkey === 'AmountLimitPerTimePeriod') && $key === 'CurrencyCode')
                    $this->AmountLimitCurrencyCode = $value;
                    
                elseif (($Parentkey === 'CurrentRemainingBalance') && $key === 'Amount')
                    $this->BalanceAmount = $value;
                    
                elseif (($Parentkey === 'CurrentRemainingBalance') && $key === 'CurrencyCode')
                    $this->BalanceAmountCurrencyCode = $value;
                
                elseif ($key === 'TimePeriodStartDate')
                    $this->TimePeriodStartDate = $value;
                
                elseif ($key === 'TimePeriodEndDate')
                    $this->TimePeriodStartDate = $value;
                elseif ($key === 'LastUpdatedTimestamp')
                    $this->LastUpdatedTimestamp = $value;
                
                elseif ($key === 'Phone')
                    $this->Phone = $value;
                
                elseif ($key === 'Email')
                    $this->Email = $value;
                
                elseif ($key === 'Name')
                    $this->Name = $value;
                    
                elseif ($key === 'AddressLine1')
                    $this->AddressLine1 = $value;
                
                elseif ($key === 'AddressLine2')
                    $this->AddressLine2 = $value;
                
                elseif ($key === 'AddressLine3')
                    $this->AddressLine3 = $value;
                    
                elseif ($key === 'PostalCode')
                    $this->PostalCode = $value;
                
                elseif ($key === 'CountryCode')
                    $this->CountryCode = $value;
                
                elseif ($key === 'StateOrRegion')
                    $this->StateOrRegion = $value;
                
                elseif ($key === 'City')
                    $this->City = $value;
                
                elseif ($key === 'County')
                    $this->County = $value;
                
                elseif ($key === 'District')
                    $this->District = $value;
                
                elseif ($key === 'DestinationType')
                    $this->DestinationType = $value;
                        
                 elseif ($key === 'StoreName')
                    $this->StoreName = $value;
                
                elseif ($key === 'SellerNote')
                    $this->SellerNote = $value;
                
                elseif ($key === 'PlatformId')
                    $this->PlatformId = $value;
                    
                elseif ($key === 'SellerBillingAgreementId')
                    $this->SellerBillingAgreementId = $value;
                elseif ($key === 'StoreName')
                    $this->StoreName = $value;
                
                elseif ($key === 'SellerOrderId') {
                    $this->SellerOrderId = $value;
                }
                
                $this->isBillingAgreementDetailsSuccess = true;
            }
            
        } else {
            throw new Exception("Response is Empty");
        }
    }
}

class ValidateBillingAgreementResponse
{
    
    public $ValidationResult = null;
    public $FailureReasonCode = null;
    public $BillingAgreementState = null;
    
    public $ReasonCode = null;
    public $ReasonDescription = null;
    public $LastUpdatedTimestamp = null;
    
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $isValidateBASuccess = false;
    
    public function __construct($response)
    {
        $iterator     = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        $ErrorDetails = new ErrorResponse($response);
        
        if ($ErrorDetails->IsError == true) {
            $this->ErrorCode    = $ErrorDetails->ErrorCode;
            $this->ErrorMessage = $ErrorDetails->ErrorMessage;
            
        } elseif (isset($response['ValidateBillingAgreementResult'])) {
            foreach ($iterator as $key => $value) {
                
                $Parentkey = $iterator->getSubIterator($iterator->getDepth() - 1)->key();
                
                if ($key === 'ValidationResult')
                    $this->ValidationResult = $value;
                    
                elseif ($key === 'FailureReasonCode')
                    $this->FailureReasonCode = $value;
                    
                elseif ($key === 'State')
                    $this->BillingAgreementState = $value;
                
                elseif ($key === 'ReasonCode')
                    $this->ReasonCode = $value;
                
                elseif ($key === 'ReasonDescription')
                    $this->ReasonDescription = $value;
                
                elseif ($key === 'LastUpdatedTimestamp')
                    $this->LastUpdatedTimestamp = $value;
            }
            
            $this->isValidateBASuccess = true;
        }
        
    }
}

class ErrorResponse
{
    public $ErrorCode = null;
    public $ErrorMessage = null;
    public $IsError = false;
    
    public function __construct($response)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($response));
        
        if (isset($response['Error'])) {
            
            foreach ($iterator as $key => $value) {
                
                if ($key === 'Code')
                    $this->ErrorCode = $value;
                if ($key === 'Message')
                    $this->ErrorMessage = $value;
            }
            $this->IsError = true;
        }
    }
}