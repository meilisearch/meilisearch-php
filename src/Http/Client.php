<?php

declare(strict_types=1);

namespace MeiliSearch\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;
use MeiliSearch\Exceptions\InvalidResponseBodyException;
use MeiliSearch\Exceptions\JsonDecodingException;
use MeiliSearch\Exceptions\JsonEncodingException;
use MeiliSearch\Http\Serialize\Json;
use MeiliSearch\MeiliSearch;
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
    private array $headers;
    private ?string $apiKey;
    private string $baseUrl;
    private Json $json;

    /**
     * Client constructor.
     */
    public function __construct(
        string $url,
        string $apiKey = null,
        ClientInterface $httpClient = null,
        RequestFactoryInterface $reqFactory = null,
        array $clientAgents = []
    ) {
        $this->baseUrl = $url;
        $this->apiKey = $apiKey;
        $this->http = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $reqFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->headers = array_filter([
            'User-Agent' => implode(';', array_merge($clientAgents, [MeiliSearch::qualifiedVersion()])),
        ]);
        if (null != $this->apiKey) {
            $this->headers['Authorization'] = sprintf('Bearer %s', $this->apiKey);
        }
        $this->json = new Json();
    }

    /**
     * @return mixed
     *
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
     * @return mixed
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

    public function put(string $path, $body = null, array $query = [])
    {
        $this->headers['Content-type'] = 'application/json';
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($this->json->serialize($body)));

        return $this->execute($request);
    }

    /**
     * @param mixed|null $body
     *
     * @return mixed
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
     * @return mixed
     *
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
     * @return mixed
     *
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
     * @return mixed
     *
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
