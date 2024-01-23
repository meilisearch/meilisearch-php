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

        self::assertSame($expectedKeys, array_keys($task));
        self::assertSame('snapshotCreation', $task['type']);
    }
}
