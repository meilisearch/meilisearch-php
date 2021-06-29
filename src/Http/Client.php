<?php

declare(strict_types=1);

namespace MeiliSearch\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;
use MeiliSearch\Exceptions\FailedJsonEncodingException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
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
    public function __construct(string $url, string $apiKey = null, ClientInterface $httpClient = null)
    {
        $this->baseUrl = $url;
        $this->apiKey = $apiKey;
        $this->http = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->headers = array_filter([
            'Content-type' => 'application/json',
            'X-Meili-API-Key' => $this->apiKey,
        ]);
    }

    /**
     * @param array $query
     *
     * @return mixed
     *
     * @throws ClientExceptionInterface
     * @throws ApiException
     * @throws CommunicationException
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
     * @param mixed|null $body
     *
     * @return mixed
     *
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     * @throws FailedJsonEncodingException
     */
    public function post(string $path, $body = null, array $query = [])
    {
        $content = json_encode($body);

        if (false === $content) {
            throw new FailedJsonEncodingException('Encoding payload to json failed. '.json_last_error_msg());
        }

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($content));

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
     * @throws ApiException
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
     * @throws ApiException
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
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     * @throws ApiException
     */
    private function execute(RequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        try {
            return $this->parseResponse($this->http->sendRequest($request));
        } catch (NetworkExceptionInterface $e) {
            throw new CommunicationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function buildQueryString(array $queryParams = []): string
    {
        return \count($queryParams) > 0 ? '?'.http_build_query($queryParams) : '';
    }

    /**
     * @return mixed
     *
     * @throws ApiException
     */
    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 300) {
            $body = json_decode($response->getBody()->getContents(), true) ?? $response->getReasonPhrase();
            throw new ApiException($response, $body);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
