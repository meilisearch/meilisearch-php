<?php

declare(strict_types=1);

namespace MeiliSearch\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use const JSON_THROW_ON_ERROR;
use JsonException;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;
use MeiliSearch\Exceptions\FailedJsonDecodingException;
use MeiliSearch\Exceptions\FailedJsonEncodingException;
use MeiliSearch\Exceptions\InvalidResponseBodyException;
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
     * @var ClientInterface
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
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($this->jsonEncode($body)));

        return $this->execute($request);
    }

    public function put($path, $body = null, $query = [])
    {
        $request = $this->requestFactory->createRequest(
            'PUT',
            $this->baseUrl.$path.$this->buildQueryString($query)
        )->withBody($this->streamFactory->createStream($this->jsonEncode($body)));

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
        )->withBody($this->streamFactory->createStream($this->jsonEncode($body)));

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
        if (204 === $response->getStatusCode()) {
            return null;
        }

        if (!\in_array('application/json', $response->getHeader('content-type'), true)) {
            throw new InvalidResponseBodyException($response, $response->getBody()->getContents());
        }

        if ($response->getStatusCode() >= 300) {
            $body = $this->jsonDecode($response->getBody()->getContents()) ?? $response->getReasonPhrase();

            throw new ApiException($response, $body);
        }

        return $this->jsonDecode($response->getBody()->getContents());
    }

    /**
     * @param mixed|null $body
     */
    private function jsonEncode($body): string
    {
        try {
            $encoded = json_encode($body, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FailedJsonEncodingException(sprintf('Encoding payload to json failed: "%s".', $e->getMessage()), $e->getCode(), $e);
        }

        return $encoded;
    }

    /**
     * @return mixed
     */
    private function jsonDecode(string $content)
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FailedJsonDecodingException(sprintf('Decoding json payload failed: "%s".', $e->getMessage()), $e->getCode(), $e);
        }

        return $decoded;
    }
}
