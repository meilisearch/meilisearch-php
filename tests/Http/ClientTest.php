<?php

declare(strict_types=1);

namespace Tests\Http;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\CommunicationException;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function testThrowCommunicationException(): void
    {
        $client = new Client('http://wrongurl:1234');

        $this->expectException(CommunicationException::class);
        $client->createIndex('some_index');
    }
}
