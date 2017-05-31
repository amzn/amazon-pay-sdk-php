<?php
namespace AmazonPay;

require_once 'AmazonPay/IpnHandler.php';

class IpnHandlertest extends \PHPUnit_Framework_TestCase
{
    private $configParams = array(
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

            $ipnHandler = new IpnHandler($headers,$body,$this->configParams);

        } catch (\Exception $expected) {
            $this->assertRegExp('/Error with message - header./i', strval($expected));
        }
        try {
            $headers['x-amz-sns-message-type'] = 'Notification';
            $body = 'abctest';

            $ipnHandler = new IpnHandler($headers,$body,$this->configParams);

        } catch (\Exception $expected) {
            $this->assertRegExp('/Error with message - content is not in json format./i', strval($expected));
        }
        try {
            $ConfigParams = array(
                'a' => 'A',
                'b' => 'B'
            );

            $ipnHandler = new IpnHandler(array(),null,$ConfigParams);

        } catch (\Exception $expected) {
            $this->assertRegExp('/is either not part of the configuration or has incorrect Key name./i', strval($expected));
        }
    }

    public function testValidateUrl()
    {
      $headers = array('x-amz-sns-message-type' => 'Notification');
      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"http://sns.us-east-1.amazonaws.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }

      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"https://sns.us-east-1.amazonaws.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.exe"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }

      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"https://sns.us-east-1.example.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }

      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"https://sni.us-east-1.amazonaws.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }

      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"https://sns.us.amazonaws.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }

      try {
        $body = '{"Type":"Notification", "Message":"Test", "MessageId":"Test", "Timestamp":"Test", "Subject":"Test", "TopicArn":"Test", "Signature":"Test", "SigningCertURL":"https://sns.us-east-1.amazonaws.com.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}';
        $ipnHandler = new IpnHandler($headers, $body, $this->configParams);
      } catch (\Exception $expected) {
        $this->assertRegExp('/The certificate is located on an invalid domain./i', strval($expected));
      }
    }
}
