<?php
namespace AmazonPay;

/* Interface for IpnHandler.php */

interface IpnHandlerInterface
{   
    /* returnMessage() - JSON decode the raw [Message] portion of the IPN */
    
    public function returnMessage();

    /* toJson() - Converts IPN [Message] field to JSON
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML notification data
     * @param remainingFields - consists of remaining IPN array fields that are merged
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in JSON format
     */
    
    public function toJson();

    /* toArray() - Converts IPN [Message] field to associative array
     * @return response in array format
     */
    
    public function toArray();
}
