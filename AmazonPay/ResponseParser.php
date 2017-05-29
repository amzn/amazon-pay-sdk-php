<?php

namespace AmazonPay;

require_once 'ResponseInterface.php';

/**
 * Methods provided to convert the Response from the POST to XML, Array or JSON
 */
class ResponseParser implements ResponseInterface
{
    public $response = null;

    /**
     * @param null $response
     */
    public function __construct($response = null)
    {
        $this->response = $response;
    }

    /**
     * @inheritdoc
     */
    public function toXml()
    {
        return $this->response['ResponseBody'];
    }

    /**
     * @inheritdoc
     */
    public function toJson()
    {
        $response = $this->simpleXmlObject();

        return json_encode($response);
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $response = $this->simpleXmlObject();

        // Converting the SimpleXMLElement Object to array()
        $response = json_encode($response);

        return json_decode($response, true);
    }

    /**
     * @return null|\SimpleXMLElement
     */
    private function simpleXmlObject()
    {
        $response = $this->response;

        // Getting the HttpResponse Status code to the output as a string
        $status = strval($response['Status']);

        // Getting the Simple XML element object of the XML Response Body
        $response = simplexml_load_string((string) $response['ResponseBody']);

        // Adding the HttpResponse Status code to the output as a string
        $response->addChild('ResponseStatus', $status);

        return $response;
    }

    /**
     * Get the status of the Order Reference ID
     *
     * @param $response
     *
     * @return mixed
     */
    public function getOrderReferenceDetailsStatus($response)
    {
        return $this->getStatus('GetORO', '//GetORO:OrderReferenceStatus', $response);
    }

    /**
     * Get the status of the BillingAgreement
     *
     * @param $response
     *
     * @return mixed
     */
    public function getBillingAgreementDetailsStatus($response)
    {
        return $this->getStatus('GetBA', '//GetBA:BillingAgreementStatus', $response);
    }

    /**
     * @param $type
     * @param $path
     * @param $response
     *
     * @return mixed
     */
    private function getStatus($type, $path, $response)
    {
        $data = new \SimpleXMLElement($response);
        $namespaces = $data->getNamespaces(true);
        foreach ($namespaces as $key => $value) {
            $namespace = $value;
        }
        $data->registerXPathNamespace($type, $namespace);
        foreach ($data->xpath($path) as $value) {
            $status = json_decode(json_encode((array) $value), true);
        }

        return $status;
    }
}
