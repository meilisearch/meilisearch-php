<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;

final class MockTask
{
    public static function create(
        TaskType $type,
        int $taskUid = 1,
        ?string $indexUid = null,
        TaskStatus $status = TaskStatus::Enqueued,
        \DateTimeImmutable $enqueuedAt = new \DateTimeImmutable('2025-04-09T07:09:13.867326401Z'),
        ?\Closure $await = null,
    ): Task {
        return new Task($taskUid, $indexUid, $status, $type, $enqueuedAt, await: $await);
    }
}
