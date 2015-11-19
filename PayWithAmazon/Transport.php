<?php
namespace PayWithAmazon;

require_once 'HttpCurl.php';

/**
 * Used to run real requests.
 */
class CurlTransport
{
    public function send($params)
    {
        $userAgent = isset($params['userAgent']) ? $params['userAgent'] : null;
        $userAgent = $userAgent ?: null;

        $httpCurlRequest = new HttpCurl($params['config']);
        if (isset($params['headers'])) {
            $httpCurlRequest->setHttpHeaders($params['headers']);
        }

        if ($params['method'] == "POST") {
            $response = $httpCurlRequest->httpPost($params['url'], $userAgent, $params['parameters']);
        } else if ($params['method'] == "GET") {
            $response = $httpCurlRequest->httpGet($params['url'], $userAgent);
        } else {
            throw new \InvalidArgumentException("no such method: {$params['method']}");
        }

        $curlResponseInfo = $httpCurlRequest->getCurlResponseInfo();

        $statusCode = $curlResponseInfo["http_code"];

        return array(
            'body' => $response,
            'status' => $statusCode,
        );
    }
}

/**
 * Used for unit testing.
 */
class MockTransport
{
    public function __construct()
    {
        $this->requests = array();
        $this->responses = array();
    }

    public function pushResponse($body, $status=200)
    {
        $this->responses[] = array(
            'body' => $body,
            'status' => $status,
        );
    }

    public function send($params)
    {
        $this->requests[] = $params;

        if ($this->responses) {
            return array_shift($this->responses);
        } else {
            throw new \Exception("no responses to respond with");
        }
    }
}
