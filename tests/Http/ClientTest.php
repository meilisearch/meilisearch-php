<?php

declare(strict_types=1);

namespace Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\CommunicationException;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function testThrowCommunicationException(): void
    {
        $mock = new MockHandler([
            new ConnectException('MeiliSearch instance is unreachable', new Request('POST', '/dumps')),
        ]);
        $mockedHttpClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new Client(self::HOST, self::DEFAULT_KEY, $mockedHttpClient);

        $this->expectException(CommunicationException::class);
        $this->expectErrorMessage('MeiliSearch instance is unreachable');
        $client->health();
    }
}
