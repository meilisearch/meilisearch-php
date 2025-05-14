<?php

declare(strict_types=1);

namespace Tests\Contracts;

use MeiliSearch\Contracts\Task;
use MeiliSearch\Contracts\TaskStatus;
use MeiliSearch\Contracts\TaskType;
use PHPUnit\Framework\TestCase;
use Tests\MockTask;

final class TaskTest extends TestCase
{
    public function testCreate(): void
    {
        $task = new Task(
            taskUid: 1,
            indexUid: 'documents',
            status: TaskStatus::Failed,
            type: TaskType::IndexCreation,
            enqueuedAt: new \DateTimeImmutable('2025-04-09 10:28:12.236789123Z'),
            startedAt: new \DateTimeImmutable('2025-04-09 10:28:12.555566'),
            finishedAt: new \DateTimeImmutable('2025-04-09 10:28:12.666677'),
            duration: 'PT0.184408715S',
            canceledBy: 123,
            batchUid: 666,
            details: ['some' => 'detail'],
            error: [
                'message' => 'Index `documents` not found.',
                'code' => 'index_not_found',
                'type' => 'invalid_request',
                'link' => 'https://docs.meilisearch.com/errors#index_not_found',
            ],
            data: [
                'taskUid' => 1,
                'indexUid' => 'documents',
                'status' => 'failed',
                'type' => 'index_creation',
                'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            ],
        );

        self::assertSame(1, $task->getTaskUid());
        self::assertSame('documents', $task->getIndexUid());
        self::assertSame(TaskStatus::Failed, $task->getStatus());
        self::assertSame(TaskType::IndexCreation, $task->getType());
        self::assertEquals(new \DateTimeImmutable('+2025-04-09T10:28:12.236789'), $task->getEnqueuedAt());
        self::assertEquals(new \DateTimeImmutable('+2025-04-09T10:28:12.555566+0000'), $task->getStartedAt());
        self::assertEquals(new \DateTimeImmutable('+2025-04-09T10:28:12.666677+0000'), $task->getFinishedAt());
        self::assertSame('PT0.184408715S', $task->getDuration());
        self::assertSame(123, $task->getCanceledBy());
        self::assertSame(666, $task->getBatchUid());
        self::assertSame(['some' => 'detail'], $task->getDetails());
        self::assertSame([
            'message' => 'Index `documents` not found.',
            'code' => 'index_not_found',
            'type' => 'invalid_request',
            'link' => 'https://docs.meilisearch.com/errors#index_not_found',
        ], $task->getError());

        // Ensure the class supports array access retrocompatibility
        self::assertSame(1, $task['taskUid']);
        self::assertSame('documents', $task['indexUid']);
        self::assertSame('failed', $task['status']);
        self::assertSame('index_creation', $task['type']);
        self::assertSame('2025-04-09T10:28:12.236789123Z', $task['enqueuedAt']);
    }

    public function testCreateEnqueuedTask(): void
    {
        $task = new Task(
            taskUid: 1,
            indexUid: 'documents',
            status: TaskStatus::Enqueued,
            type: TaskType::IndexCreation,
            enqueuedAt: new \DateTimeImmutable('2025-04-09 10:28:12.236789'),
        );

        self::assertSame(1, $task->getTaskUid());
        self::assertSame('documents', $task->getIndexUid());
        self::assertSame(TaskStatus::Enqueued, $task->getStatus());
        self::assertSame(TaskType::IndexCreation, $task->getType());
        self::assertEquals(new \DateTimeImmutable('+2025-04-09T10:28:12.236789+0000'), $task->getEnqueuedAt());
        self::assertNull($task->getStartedAt());
        self::assertNull($task->getFinishedAt());
        self::assertNull($task->getDuration());
        self::assertNull($task->getCanceledBy());
        self::assertNull($task->getBatchUid());
        self::assertNull($task->getDetails());
        self::assertNull($task->getError());
    }

    public function testArraySetThrows(): void
    {
        $task = MockTask::create(TaskType::IndexCreation);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Setting data on "MeiliSearch\Contracts\Task::type" is not supported.');

        $task['type'] = TaskType::IndexDeletion;
    }

    public function testArrayUnsetThrows(): void
    {
        $task = MockTask::create(TaskType::IndexCreation);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsetting data on "MeiliSearch\Contracts\Task::type" is not supported.');

        unset($task['type']);
    }
}
