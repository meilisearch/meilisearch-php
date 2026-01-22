<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskDetails\IndexCreationDetails;
use Meilisearch\Contracts\TaskError;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
use Meilisearch\Exceptions\LogicException;
use PHPUnit\Framework\Attributes\TestWith;
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
            details: new IndexCreationDetails('custom_id'),
            error: new TaskError(
                'Index `documents` not found.',
                'index_not_found',
                'invalid_request',
                'https://docs.meilisearch.com/errors#index_not_found',
            ),
            raw: ['taskUid' => 1],
        );

        self::assertSame(1, $task->getTaskUid());
        self::assertSame('documents', $task->getIndexUid());
        self::assertSame(TaskStatus::Failed, $task->getStatus());
        self::assertSame(TaskType::IndexCreation, $task->getType());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.236789'), $task->getEnqueuedAt());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.555566+0000'), $task->getStartedAt());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.666677+0000'), $task->getFinishedAt());
        self::assertSame('PT0.184408715S', $task->getDuration());
        self::assertSame(123, $task->getCanceledBy());
        self::assertSame(666, $task->getBatchUid());
        self::assertEquals(new IndexCreationDetails('custom_id'), $task->getDetails());
        self::assertEquals(new TaskError(
            'Index `documents` not found.',
            'index_not_found',
            'invalid_request',
            'https://docs.meilisearch.com/errors#index_not_found',
        ), $task->getError());

        self::assertTrue(isset($task['taskUid']));
        self::assertSame(1, $task['taskUid']);
        self::assertSame(['taskUid' => 1], $task->toArray());
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
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.236789+0000'), $task->getEnqueuedAt());
        self::assertNull($task->getStartedAt());
        self::assertNull($task->getFinishedAt());
        self::assertNull($task->getDuration());
        self::assertNull($task->getCanceledBy());
        self::assertNull($task->getBatchUid());
        self::assertNull($task->getDetails());
        self::assertNull($task->getError());
    }

    public function testWait(): void
    {
        $await = static function () {
            return MockTask::create(TaskType::IndexCreation, status: TaskStatus::Succeeded);
        };
        $task = MockTask::create(TaskType::IndexCreation, await: $await(...));
        $completedTask = $task->wait();

        self::assertNotSame($task, $completedTask);
        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
    }

    #[TestWith([TaskStatus::Succeeded])]
    #[TestWith([TaskStatus::Failed])]
    #[TestWith([TaskStatus::Canceled])]
    public function testWaitReturnsImmediatelyIfTaskIsAlreadyFinished(TaskStatus $status): void
    {
        $task = MockTask::create(TaskType::IndexCreation, status: $status);

        self::assertSame($task, $task->wait());
    }

    public function testWaitThrowsWithoutFunction(): void
    {
        $task = MockTask::create(TaskType::IndexCreation);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot wait for task because wait function is not provided.');

        $task->wait();
    }

    public function testOffsetSetThrows(): void
    {
        $task = MockTask::create(TaskType::DocumentAdditionOrUpdate);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot set data on Task');

        $task['taskUid'] = 2;
    }

    public function testOffsetUnsetThrows(): void
    {
        $task = MockTask::create(TaskType::DocumentAdditionOrUpdate);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot unset data from Task');

        unset($task['taskUid']);
    }
}
