<?php

declare(strict_types=1);

namespace Tests\Http;

use Http\Client\Exception\NetworkException;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\CommunicationException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function testThrowCommunicationException(): void
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $networkException = new NetworkException('testThrowCommunicationException', $request);
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)->getMock();
        $mockedHttpClient
            ->method('sendRequest')
            ->willThrowException($networkException);
        $client = new Client(self::HOST, self::DEFAULT_KEY, $mockedHttpClient);

        $this->expectException(CommunicationException::class);
        $this->expectErrorMessage('testThrowCommunicationException');
        $client->health();
    }
}
