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
    /**
     * @var array<string, string|string[]>
     */
    private array $headers;
    /**
     * @var non-empty-string
     */
    private string $baseUrl;
    private Json $json;

    /**
     * @param non-empty-string   $url
     * @param array<int, string> $clientAgents
     */
    public function __construct(
        string $url,
        ?string $apiKey = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $reqFactory = null,
        array $clientAgents = [],
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->baseUrl = $url;
        $this->http = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $reqFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->headers = [
            'User-Agent' => implode(';', array_merge($clientAgents, [Meilisearch::qualifiedVersion()])),
        ];
        if (null !== $apiKey && '' !== $apiKey) {
            $this->headers['Authorization'] = \sprintf('Bearer %s', $apiKey);
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
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     * @throws JsonEncodingException
     */
    public function post(string $path, $body = null, array $query = [], ?string $contentType = null)
    {
        if (null === $contentType) {
            $body = $this->json->serialize($body);
        }
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($body));

        return $this->execute($request, ['Content-type' => $contentType ?? 'application/json']);
    }

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     * @throws JsonEncodingException
     */
    public function put(string $path, $body = null, array $query = [], ?string $contentType = null)
    {
        if (null === $contentType) {
            $body = $this->json->serialize($body);
        }
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($body));

        return $this->execute($request, ['Content-type' => $contentType ?? 'application/json']);
    }

    public function patch(string $path, $body = null, array $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'PATCH',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($this->json->serialize($body)));

        return $this->execute($request, ['Content-type' => 'application/json']);
    }

    public function delete(string $path, array $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'DELETE',
            $this->baseUrl.$path.$this->buildQueryString($query)
        );

        return $this->execute($request);
    }

    /**
     * @param array<string, string|string[]> $headers
     *
     * @throws ApiException
     * @throws ClientExceptionInterface
     * @throws CommunicationException
     */
    private function execute(RequestInterface $request, array $headers = [])
    {
        foreach (array_merge($this->headers, $headers) as $header => $value) {
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

        if (!$this->isJSONResponse($response->getHeader('content-type'))) {
            throw new InvalidResponseBodyException($response, (string) $response->getBody());
        }

        if ($response->getStatusCode() >= 300) {
            $body = $this->json->unserialize((string) $response->getBody()) ?? $response->getReasonPhrase();

            throw new ApiException($response, $body);
        }

        return $this->json->unserialize((string) $response->getBody());
    }

    /**
     * Checks if any of the header values indicate a JSON response.
     *
     * @param array $headerValues the array of header values to check
     *
     * @return bool true if any header value contains 'application/json', otherwise false
     */
    private function isJSONResponse(array $headerValues): bool
    {
        $filteredHeaders = array_filter($headerValues, static function (string $headerValue) {
            return false !== strpos($headerValue, 'application/json');
        });

        return \count($filteredHeaders) > 0;
    }
}
