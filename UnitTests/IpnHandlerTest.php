<?php

require_once '../OffAmazonPayments/IpnHandler.php';

class IpnHandlertest extends PHPUnit_Framework_TestCase
{
    private $_configParams = array(
                'cabundle_file'  => null,
                'proxy_host'     => null,
                'proxy_port'     => -1,
                'proxy_username' => null,
                'proxy_Password' => null
            );
   
    public function testConstructor()
    {
        try {
            $headers = array();
            $headers = array('ab'=>'abc');
            $body = 'abctest';
            
            $ipnHandler = new IpnHandler($headers,$body,$this->_configParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/Error with message - header./i', strval($expected));
        }
        try {
            $headers['x-amz-sns-message-type'] = 'Notification';
            $body = 'abctest';
            
            $ipnHandler = new IpnHandler($headers,$body,$this->_configParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/Error with message - content is not in json format./i', strval($expected));
        }
        try {
            
            $ConfigParams = array(
                'a' => 'A',
                'b' => 'B'
            );
            
            $ipnHandler = new IpnHandler(array(),null,$ConfigParams);
        }
        catch (Exception $expected) {
            $this->assertRegExp('/is either not part of the configuration or has incorrect Key name./i', strval($expected));
        }
    }
    
    
}