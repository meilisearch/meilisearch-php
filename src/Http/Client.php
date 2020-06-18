<?php


namespace MeiliSearch\Http;


use MeiliSearch\Contracts\Http;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientExceptionInterface;
use MeiliSearch\Exceptions\HTTPRequestException;

class Client implements Http
{
    /**
     * @var  Http
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


    public function __construct($url, $apiKey = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->baseUrl = $url;
        $this->apiKey = $apiKey;
        $this->headers = array_filter([
            'Content-type' => 'application/json',
            'X-Meili-API-Key' => $this->apiKey,
        ]);

        $this->http = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    public function get($path, $query = [])
    {
        $request    = $this->requestFactory->createRequest(
            'GET',
            $this->baseUrl . $path . $this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    public function post($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl . $path. $this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->execute($request);
    }

    public function put($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl . $path. $this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream(json_encode($body)));

        return $this->execute($request);
    }

    public function patch()
    {
        // TODO: Implement patch() method.
    }

    public function delete($path, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'DELETE',
            $this->baseUrl . $path. $this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    private function execute(RequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        return $this->parseResponse($this->http->sendRequest($request));
    }

    private function buildQueryString(array $queryParams = []): string
    {
        return $queryParams ? '?' . http_build_query($queryParams) : '';
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws HTTPRequestException@
     */
    private function parseResponse(ResponseInterface $response): ?array
    {
        if ($response->getStatusCode() >= 300) {
            $body = json_decode($response->getBody()->getContents(), true);
            throw new HTTPRequestException($response->getStatusCode(), $body);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}