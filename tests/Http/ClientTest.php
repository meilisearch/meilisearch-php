<?php

declare(strict_types=1);

namespace Tests\Http;

use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\FailedJsonEncodingException;
use MeiliSearch\Http\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ClientTest extends TestCase
{
    public function testPostThrowsWithInvalidBody(): void
    {
        $client = new Client('https://localhost');

        $this->expectException(FailedJsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Malformed UTF-8 characters, possibly incorrectly encoded".');

        $client->post('/', "{'Bad JSON':\xB1\x31}");
    }

    /**
     * @dataProvider provideStatusCodes
     */
    public function testPostThrowsWithInvalidResponse(int $statusCode): void
    {
        $httpClient = $this->createHttpClientMock($statusCode);

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Decoding json payload failed: "Syntax error".');

        $client->post('/', '');
    }

    public function testPostThrowsApiException(): void
    {
        $httpClient = $this->createHttpClientMock(300, '{"message":"internal error"}');

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('internal error');

        $client->post('/', '');
    }

    public function testPutThrowsWithInvalidBody(): void
    {
        $client = new Client('https://localhost');

        $this->expectException(FailedJsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Malformed UTF-8 characters, possibly incorrectly encoded".');

        $client->put('/', "{'Bad JSON':\xB1\x31}");
    }

    /**
     * @dataProvider provideStatusCodes
     */
    public function testPutThrowsWithInvalidResponse(int $statusCode): void
    {
        $httpClient = $this->createHttpClientMock($statusCode);

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Decoding json payload failed: "Syntax error".');

        $client->put('/', '');
    }

    public function testPutThrowsApiException(): void
    {
        $httpClient = $this->createHttpClientMock(300, '{"message":"internal error"}');

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('internal error');

        $client->put('/', '');
    }

    public function testPatchThrowsWithInvalidBody(): void
    {
        $client = new Client('https://localhost');

        $this->expectException(FailedJsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Malformed UTF-8 characters, possibly incorrectly encoded".');

        $client->patch('/', "{'Bad JSON':\xB1\x31}");
    }

    /**
     * @dataProvider provideStatusCodes
     */
    public function testPatchThrowsWithInvalidResponse(int $statusCode): void
    {
        $httpClient = $this->createHttpClientMock($statusCode);

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Decoding json payload failed: "Syntax error".');

        $client->put('/', '');
    }

    public function testPatchThrowsApiException(): void
    {
        $httpClient = $this->createHttpClientMock(300, '{"message":"internal error"}');

        $client = new Client('https://localhost', null, $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('internal error');

        $client->post('/', '');
    }

    public function provideStatusCodes(): iterable
    {
        yield [200];
        yield [300];
        yield [301];
    }

    /**
     * @return ClientInterface|MockObject
     */
    private function createHttpClientMock(int $status = 200, string $content = '{')
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects(self::once())
            ->method('getContents')
            ->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())
            ->method('getStatusCode')
            ->willReturn($status);
        $response->expects(self::any())
            ->method('getReasonPhrase')
            ->willReturn('HTTP Status '.$status);
        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with(self::isInstanceOf(RequestInterface::class))
            ->willReturn($response);

        return $httpClient;
    }
}
