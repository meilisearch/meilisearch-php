<?php

declare(strict_types=1);

namespace Meilisearch\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Meilisearch\Contracts\Http;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;
use Meilisearch\Exceptions\InvalidResponseBodyException;
use Meilisearch\Exceptions\JsonDecodingException;
use Meilisearch\Exceptions\JsonEncodingException;
use Meilisearch\Http\Serialize\Json;
use Meilisearch\Meilisearch;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client implements Http
{
    private ClientInterface $http;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    /** @var array<string,string> */
    private array $headers;
    private string $baseUrl;
    private Json $json;

    /**
     * @param array<int, string> $clientAgents
     */
    public function __construct(
        string $url,
        string $apiKey = null,
        ClientInterface $httpClient = null,
        RequestFactoryInterface $reqFactory = null,
        array $clientAgents = [],
        StreamFactoryInterface $streamFactory = null
    ) {
        $this->baseUrl = $url;
        $this->http = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $reqFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->headers = array_filter([
            'User-Agent' => implode(';', array_merge($clientAgents, [Meilisearch::qualifiedVersion()])),
        ]);
        if (null !== $apiKey && '' !== $apiKey) {
            $this->headers['Authorization'] = sprintf('Bearer %s', $apiKey);
        }
        $this->json = new Json();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ApiException
     * @throws CommunicationException
     */
    public function get(string $path, array $query = [])
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
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     * @throws JsonEncodingException
     */
    public function post(string $path, $body = null, array $query = [], string $contentType = null)
    {
        if (!\is_null($contentType)) {
            $this->headers['Content-type'] = $contentType;
        } else {
            $this->headers['Content-type'] = 'application/json';
            $body = $this->json->serialize($body);
        }
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($body));

        return $this->execute($request);
    }

    public function put(string $path, $body = null, array $query = [], string $contentType = null)
    {
        if (!\is_null($contentType)) {
            $this->headers['Content-type'] = $contentType;
        } else {
            $this->headers['Content-type'] = 'application/json';
            $body = $this->json->serialize($body);
        }
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($body));

        return $this->execute($request);
    }

    /**
     * @param mixed|null $body
     *
     * @throws ApiException
     * @throws JsonEncodingException
     */
    public function patch(string $path, $body = null, array $query = [])
    {
        $this->headers['Content-type'] = 'application/json';
        $request = $this->requestFactory->createRequest(
            'PATCH',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($this->json->serialize($body)));

        return $this->execute($request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ApiException
     */
    public function delete(string $path, array $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'DELETE',
            $this->baseUrl.$path.$this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    /**
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
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
     * @throws ApiException
     * @throws InvalidResponseBodyException
     * @throws JsonDecodingException
     */
    private function parseResponse(ResponseInterface $response)
    {
        if (204 === $response->getStatusCode()) {
            return null;
        }

        if (!\in_array('application/json', $response->getHeader('content-type'), true)) {
            throw new InvalidResponseBodyException($response, $response->getBody()->getContents());
        }

        if ($response->getStatusCode() >= 300) {
            $body = $this->json->unserialize($response->getBody()->getContents()) ?? $response->getReasonPhrase();

            throw new ApiException($response, $body);
        }

        return $this->json->unserialize($response->getBody()->getContents());
    }
}
