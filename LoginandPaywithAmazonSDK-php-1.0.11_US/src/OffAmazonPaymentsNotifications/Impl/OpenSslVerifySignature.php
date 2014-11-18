<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License .
 * *****************************************************************************
 */

require_once 'OffAmazonPaymentsNotifications/Impl/VerifySignature.php';
require_once 'OffAmazonPaymentsNotifications/InvalidMessageException.php';
require_once 'OffAmazonPaymentsNotifications/Impl/Certificate.php';

/**
 * OpenSSL Implemntation of the verify signature algorithm
 *
 */
class OpenSslVerifySignature implements VerifySignature
{
    /**
     * Create a new instance of the openssl implementation of
     * verify signature
     * 
     * @return void
     */    
    public function __construct()
    {

    }

    /**
     * Verify that the signature is correct for the given data and
     * public key
     * 
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificatePath path to certificate, can be file or url
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if there 
     *                                                                is an error 
     *                                                                with the call
     * 
     * @return bool true if valid
     */
    public function verifySignatureIsCorrect($data, $signature, $certificatePath)
    {
        $cert = $this->_getCertificateFromCertifcatePath($certificatePath);
        $certificate = new Certificate($cert);

        return $this->verifySignatureIsCorrectFromCertificate($data, $signature, $certificate);
    }
    
    /**
     * Verify that the signature is correct for the given data and
     * public key
     * 
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificate     certificate object defined in Certificate.php
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if there 
     *                                                                is an error 
     *                                                                with the call
     * 
     * @return bool true if valid
     */
    public function verifySignatureIsCorrectFromCertificate($data, $signature, $certificate)
    {
        $certKey = openssl_get_publickey($certificate->getCertificate());

        if ($certKey === False) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to extract public key from cert " . $cert);
        }

        try {
            $certSubject = $certificate->getSubject();
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to verify certificate - error with the certificate subject",
                null, $ex
            );
        }

        $this->_verifyCertificateSubject($certSubject);

        $result = -1;
        try {
            $result = openssl_verify($data, $signature, $certKey, OPENSSL_ALGO_SHA1);
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to verify signature - error with the verification algorithm",
                null, $ex
            );
        } 
       
        return ($result > 0);
    }

    /**
     * Verify that certificate is issued by Amazon
     * 
     * @param array $certificateSubject certificate subject array
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return void
     */
    private function _verifyCertificateSubject($certificateSubject)
    {
        if ( (strcmp($certificateSubject["CN"], "sns.amazonaws.com") !== 0) ||
            ((strcmp($certificateSubject["O"], "Amazon.com Inc.") !== 0) && (strcmp($certificateSubject["O"], "Amazon.com, Inc.") !== 0)) ||
             (strcmp($certificateSubject["L"], "Seattle") !== 0) ||
             (strcmp($certificateSubject["ST"], "Washington") !== 0) ||
             (strcmp($certificateSubject["C"], "US") !== 0) ) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Unable to verify certificate issued by Amazon - error with certificate subject"
            );
        }
    }
    
    /**
     * Request the signing certificate from the given path, in order to
     * get the public key
     * 
     * @param string $certificatePath certificate path to retreive
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return void
     */
    private function _getCertificateFromCertifcatePath($certificatePath)
    {
        try {
            $cert = file_get_contents($certificatePath);
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with signature validation - unable to request signing ".
                "certificate at " . $certificatePath, null, $ex
            );
        }
            
        if ($cert === false) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with signature validation - unable to request signing ".
                "certificate at " . $certificatePath
            );
        }
            
        return $cert;
    }
}
?>
