<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Tests\TestCase;

final class DumpTest extends TestCase
{
    public function testCreateDump(): void
    {
        $expectedKeys = ['taskUid', 'indexUid', 'status', 'type', 'enqueuedAt'];

        $task = $this->client->createDump();

        $this->assertEquals($expectedKeys, array_keys($task));
    }
}
