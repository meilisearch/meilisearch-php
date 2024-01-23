<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Http\Discovery\Psr17FactoryDiscovery;
use Meilisearch\Exceptions\InvalidResponseBodyException;
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
            self::assertSame($statusCode, $invalidResponseBodyException->httpStatus);
            self::assertSame('Gateway Timeout', $invalidResponseBodyException->message);

            $expectedExceptionToString = "Meilisearch InvalidResponseBodyException: Http Status: {$statusCode} - Message: Gateway Timeout";
            self::assertSame($expectedExceptionToString, (string) $invalidResponseBodyException);
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
            self::assertSame($statusCode, $invalidResponseBodyException->httpStatus);
            self::assertSame($response->getReasonPhrase(), $invalidResponseBodyException->message);

            $expectedExceptionToString = "Meilisearch InvalidResponseBodyException: Http Status: {$statusCode} - Message: {$response->getReasonPhrase()}";
            self::assertSame($expectedExceptionToString, (string) $invalidResponseBodyException);
        }
    }
}
