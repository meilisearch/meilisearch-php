<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Http\Discovery\Psr17FactoryDiscovery;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class ApiExceptionTest extends TestCase
{
    public function testException(): void
    {
        $httpBodyExample = [
            'message' => 'This is the message',
            'errorCode' => 'this_is_the_error_code',
            'errorType' => 'this_is_the_error_type',
            'errorLink' => 'https://docs.meilisearch.com/errors',
        ];
        $statusCode = 400;

        try {
            $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
            $responseBodyStream = $streamFactory->createStream(json_encode($httpBodyExample));

            $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
            $response = $responseFactory->createResponse($statusCode)->withBody($responseBodyStream);

            throw new ApiException($response, $httpBodyExample);
        } catch (ApiException $apiException) {
            $this->assertEquals($statusCode, $apiException->httpStatus);
            $this->assertEquals($httpBodyExample['message'], $apiException->message);
            $this->assertEquals($httpBodyExample['errorCode'], $apiException->errorCode);
            $this->assertEquals($httpBodyExample['errorType'], $apiException->errorType);
            $this->assertEquals($httpBodyExample['errorLink'], $apiException->errorLink);

            $expectedExceptionToString = "MeiliSearch ApiException: Http Status: {$statusCode} - Message: {$httpBodyExample['message']} - Error code: {$httpBodyExample['errorCode']} - Error type: {$httpBodyExample['errorType']} - Error link: {$httpBodyExample['errorLink']}";
            $this->assertEquals($expectedExceptionToString, (string) $apiException);
        }
    }

    public function testExceptionWithNoHttpBody(): void
    {
        $statusCode = 400;
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $response = $responseFactory->createResponse($statusCode);

        try {
            throw new ApiException($response, null);
        } catch (ApiException $apiException) {
            $this->assertEquals($statusCode, $apiException->httpStatus);
            $this->assertEquals($response->getReasonPhrase(), $apiException->message);
            $this->assertNull($apiException->errorCode);
            $this->assertNull($apiException->errorType);
            $this->assertNull($apiException->errorLink);

            $expectedExceptionToString = "MeiliSearch ApiException: Http Status: {$statusCode} - Message: {$response->getReasonPhrase()}";
            $this->assertEquals($expectedExceptionToString, (string) $apiException);
        }
    }
}
