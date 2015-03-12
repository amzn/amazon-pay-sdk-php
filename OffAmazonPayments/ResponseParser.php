<?php

class ResponseParser
{
    public $_response = null;
    public $_xmlResponse = null;
    public function __construct($response=null)
    {
        $this->_response = $response;
        $this->_xmlResponse = $response['ResponseBody'];
    }
    

    /* toJson  - converts XML into Json
     * @param $response [XML]
     */
    public function toJson()
    {
        $response = $this->_response;
        
        //Getting the HttpResponse Status code to the output as a string
        $status = strval($response['Status']);
        
        //Getting the Simple XML element object of the XML Response Body
        $response = simplexml_load_string((string) $response['ResponseBody']);
        
        //Adding the HttpResponse Status code to the output as a string
        $response->addChild('ResponseStatus', $status);
        
        return (json_encode($response));
    }
    
    /* toArray  - converts XML into associative array
     * @param $this->_response [XML]
     */
    public function toArray()
    {
        $response = $this->_response;
        //Getting the HttpResponse Status code to the output as a string
        $status = strval($response['Status']);
        
        //Getting the Simple XML element object of the XML Response Body
        $response = simplexml_load_string((string) $response['ResponseBody']);
        
        //Adding the HttpResponse Status code to the output as a string
        $response->addChild('ResponseStatus', $status);
        
        //Converting the SimpleXMLElement Object to array()
        $response = json_encode($response);
        
        return (json_decode($response, true));
    }
}