<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Contracts\TaskType;
use Tests\TestCase;

final class SnapshotsTest extends TestCase
{
    public function testCreateSnapshots(): void
    {
        $task = $this->client->createSnapshot();

        self::assertSame(TaskType::SnapshotCreation, $task->getType());
    }
}
