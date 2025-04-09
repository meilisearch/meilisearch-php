<?php

declare(strict_types=1);

namespace Tests;

use MeiliSearch\Contracts\Task;
use MeiliSearch\Contracts\TaskStatus;
use MeiliSearch\Contracts\TaskType;

final class MockTask
{
    public static function create(
        TaskType $type,
        int $taskUid = 1,
        ?string $indexUid = null,
        TaskStatus $status = TaskStatus::Enqueued,
        \DateTimeImmutable $enqueuedAt = new \DateTimeImmutable('2025-04-09T07:09:13.867326401Z'),
    ): Task {
        return new Task($taskUid, $indexUid, $status, $type, $enqueuedAt);
    }
}
