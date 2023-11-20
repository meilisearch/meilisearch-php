<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Tests\TestCase;

final class SnapshotsTest extends TestCase
{
    public function testCreateSnapshots(): void
    {
        $expectedKeys = ['taskUid', 'indexUid', 'status', 'type', 'enqueuedAt'];

        $task = $this->client->createSnapshot();

        $this->assertSame($expectedKeys, array_keys($task));
        $this->assertSame($task['type'], 'snapshotCreation');
    }
}
