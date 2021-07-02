<?php

declare(strict_types=1);

namespace Tests;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\CommunicationException;

class ClientTest extends TestCase
{
    public function testThrowCommunicationException(): void
    {
        $client = new Client('http://wrongurl:1234');

        $this->expectException(CommunicationException::class);
        $client->createIndex('some_index');
    }
}
