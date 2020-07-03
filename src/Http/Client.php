<?php

namespace MeiliSearch\Http;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\HTTPRequestException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client implements Http
{
    /**
     * @var Http
     */
    private $http;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $apiKey;

    private $baseUrl;

    /**
     * Client constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $url, string $apiKey = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->baseUrl = $url;
        $this->apiKey = $apiKey;
        $this->http = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
        $this->headers = [
            'Content-type' => 'application/json',
            'X-Meili-API-Key' => $this->apiKey,
        ];
    }

    /**
     * @param $path
     * @param array $query
     *
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws HTTPRequestException
     */
    public function get($path, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            $this->baseUrl.$path.$this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    /**
     * @param string $path
     * @param null   $body
     * @param array  $query
     *
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws HTTPRequestException
     */
    public function post($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->execute($request);
    }

    public function put($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->execute($request);
    }

    /**
     * @param string $path
     * @param null   $body
     * @param array  $query
     *
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws HTTPRequestException
     */
    public function patch($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'PATCH',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->execute($request);
    }

    /**
     * @param $path
     * @param array $query
     *
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws HTTPRequestException
     */
    public function delete($path, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'DELETE',
            $this->baseUrl.$path.$this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    /**
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws HTTPRequestException
     */
    private function execute(RequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        return $this->parseResponse($this->http->sendRequest($request));
    }

    private function buildQueryString(array $queryParams = []): string
    {
        return $queryParams ? '?'.http_build_query($queryParams) : '';
    }

    /**
     * @return mixed
     *
     * @throws HTTPRequestException
     */
    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 300) {
            $body = json_decode($response->getBody()->getContents(), true);
            throw new HTTPRequestException($response->getStatusCode(), $body);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
