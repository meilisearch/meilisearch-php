<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Http\Discovery\Psr17FactoryDiscovery;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class ApiExceptionTest extends TestCase
{
    public function testException(): void
    {
        $httpBodyExample = [
            'message' => 'This is the message',
            'code' => 'this_is_the_error_code',
            'type' => 'this_is_the_error_type',
            'link' => 'https://docs.meilisearch.com/errors',
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
            $this->assertEquals($httpBodyExample['code'], $apiException->errorCode);
            $this->assertEquals($httpBodyExample['type'], $apiException->errorType);
            $this->assertEquals($httpBodyExample['link'], $apiException->errorLink);

            $expectedExceptionToString = "Meilisearch ApiException: Http Status: {$statusCode} - Message: {$httpBodyExample['message']} - Code: {$httpBodyExample['code']} - Type: {$httpBodyExample['type']} - Link: {$httpBodyExample['link']}";
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

            $expectedExceptionToString = "Meilisearch ApiException: Http Status: {$statusCode} - Message: {$response->getReasonPhrase()}";
            $this->assertEquals($expectedExceptionToString, (string) $apiException);
        }
    }

    public function testRethrowWithHintException(): void
    {
        $e = new \Exception('Any error message that caused the root problem should be shown');
        $apiException = ApiException::rethrowWithHint($e, 'deleteDocuments');

        $this->assertStringContainsString(
            'with the Meilisearch version that `deleteDocuments` call',
            $apiException->getMessage()
        );

        $this->assertStringContainsString(
            'Any error message that caused the root problem should be shown',
            $apiException->getPrevious()->getMessage()
        );
    }
}
