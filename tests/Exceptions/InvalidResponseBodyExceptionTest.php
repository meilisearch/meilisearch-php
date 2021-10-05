<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Http\Discovery\Psr17FactoryDiscovery;
use MeiliSearch\Exceptions\InvalidResponseBodyException;
use Tests\TestCase;

final class InvalidResponseBodyExceptionTest extends TestCase
{
    public function testException(): void
    {
        $httpBodyExample = '<b>Gateway Timeout</b>';
        $statusCode = 504;

        try {
            $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
            $responseBodyStream = $streamFactory->createStream($httpBodyExample);

            $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
            $response = $responseFactory->createResponse($statusCode)->withBody($responseBodyStream);

            throw new InvalidResponseBodyException($response, $httpBodyExample);
        } catch (InvalidResponseBodyException $invalidResponseBodyException) {
            $this->assertEquals($statusCode, $invalidResponseBodyException->httpStatus);
            $this->assertEquals('Gateway Timeout', $invalidResponseBodyException->message);

            $expectedExceptionToString = "MeiliSearch InvalidResponseBodyException: Http Status: {$statusCode} - Message: Gateway Timeout";
            $this->assertEquals($expectedExceptionToString, (string) $invalidResponseBodyException);
        }
    }

    public function testExceptionWithNoHttpBody(): void
    {
        $statusCode = 504;
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $response = $responseFactory->createResponse($statusCode);

        try {
            throw new InvalidResponseBodyException($response, null);
        } catch (InvalidResponseBodyException $invalidResponseBodyException) {
            $this->assertEquals($statusCode, $invalidResponseBodyException->httpStatus);
            $this->assertEquals($response->getReasonPhrase(), $invalidResponseBodyException->message);

            $expectedExceptionToString = "MeiliSearch InvalidResponseBodyException: Http Status: {$statusCode} - Message: {$response->getReasonPhrase()}";
            $this->assertEquals($expectedExceptionToString, (string) $invalidResponseBodyException);
        }
    }
}
