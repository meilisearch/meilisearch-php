<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\Client;
use Meilisearch\Exceptions\CommunicationException;

final class ClientTest extends TestCase
{
    public function testThrowCommunicationException(): void
    {
        $client = new Client('http://wrongurl:1234');

        $this->expectException(CommunicationException::class);
        $client->createIndex('some_index');
    }
}
