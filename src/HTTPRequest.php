<?php

namespace MeiliSearch;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use MeiliSearch\Exceptions\HTTPRequestException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HTTPRequest
{
    /**
     * @var null
     */
    public $base_url = null;

    /**
     * @var null
     */
    public $api_key = null;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct($url, $api_key = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->base_url = $url;
        $this->api_key = $api_key;
        $this->headers = array_filter([
            'Content-type' => 'application/json',
            'X-Meili-API-Key' => $this->api_key,
        ]);

        // Create default clients if necessary
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    public function httpGet($path, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->base_url.$path.$uri_query
        );

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPost($path, $body = null, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->base_url.$path.$uri_query
        );

        $request = $request->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPut($path, $body = null, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->base_url.$path.$uri_query
        );

        $request = $request->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPatch($path, $body = null)
    {
        $request = $this->requestFactory->createRequest(
            'PATCH',
            $this->base_url.$path
        );

        $request = $request->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->sendRequestAndGetBody($request);
    }

    public function httpDelete($path)
    {
        $request = $this->requestFactory->createRequest(
            'DELETE',
            $this->base_url.$path
        );

        return $this->sendRequestAndGetBody($request);
    }

    private function sendRequestAndGetBody(RequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        try {
            $res = $this->httpClient->sendRequest($request);

            // Response was not successful
            if ($res->getStatusCode() >= 300) {
                $body = json_decode($res->getBody()->getContents(), true);
                throw new HTTPRequestException($res->getStatusCode(), $body);
            }

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new HTTPRequestException(500, $e->getMessage());
        }
    }
}
