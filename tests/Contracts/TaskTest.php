<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\IndexCreationDetails;
use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
use Meilisearch\Contracts\TaskError;
use Meilisearch\Contracts\TaskNetwork\ImportNetwork;
use Meilisearch\Contracts\TaskNetwork\OriginNetwork;
use Meilisearch\Contracts\TaskNetwork\RemoteTask;
use Meilisearch\Contracts\TaskNetwork\RemoteTasksNetwork;
use Meilisearch\Contracts\TaskNetwork\TaskImportData;
use Meilisearch\Contracts\TaskNetwork\TaskOrigin;
use Meilisearch\Contracts\TaskNetwork\UnknownTaskNetwork;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
use Meilisearch\Exceptions\LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testFromArrayWithoutDetails(): void
    {
        $task = Task::fromArray([
            'taskUid' => 42,
            'indexUid' => 'movies',
            'status' => 'enqueued',
            'type' => 'indexCreation',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
        ]);

        self::assertSame(42, $task->getTaskUid());
        self::assertSame('movies', $task->getIndexUid());
        self::assertSame(TaskStatus::Enqueued, $task->getStatus());
        self::assertSame(TaskType::IndexCreation, $task->getType());
        self::assertNull($task->getDetails());
    }

    public function testFromArrayWithIndexCreationDetails(): void
    {
        $task = Task::fromArray([
            'taskUid' => 1,
            'status' => 'succeeded',
            'type' => 'indexCreation',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'details' => ['primaryKey' => 'custom_id'],
        ]);

        self::assertEquals(new IndexCreationDetails('custom_id'), $task->getDetails());
    }

    public function testFromArrayWithDocumentAdditionOrUpdateDetails(): void
    {
        $task = Task::fromArray([
            'taskUid' => 2,
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'details' => ['receivedDocuments' => 10, 'indexedDocuments' => 8],
        ]);

        self::assertEquals(new DocumentAdditionOrUpdateDetails(10, 8), $task->getDetails());
    }

    #[TestWith([TaskType::SnapshotCreation, 'snapshotCreation'])]
    #[TestWith([TaskType::NetworkTopologyChange, 'networkTopologyChange'])]
    #[TestWith([TaskType::DsrUpdate, 'dsrUpdate'])]
    #[TestWith([TaskType::DsrClear, 'dsrClear'])]
    public function testFromArrayWithNoDetailsObjectForTaskType(TaskType $expectedType, string $type): void
    {
        $task = Task::fromArray([
            'taskUid' => 3,
            'status' => 'succeeded',
            'type' => $type,
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'details' => ['ignored' => true],
        ]);

        self::assertSame($expectedType, $task->getType());
        self::assertNull($task->getDetails());
    }

    public function testFromArrayWithUnknownTypeWrapsDetails(): void
    {
        $task = Task::fromArray([
            'taskUid' => 4,
            'status' => 'succeeded',
            'type' => 'futureTaskType',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'details' => ['foo' => 'bar'],
        ]);

        self::assertSame(TaskType::Unknown, $task->getType());
        self::assertEquals(new UnknownTaskDetails(['foo' => 'bar']), $task->getDetails());
    }

    public function testFromArrayWithRemoteTasksNetwork(): void
    {
        $task = Task::fromArray([
            'taskUid' => 6,
            'indexUid' => 'movies',
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'remote_tasks' => [],
                'network_version' => '01a19dae-4677-7551-a573-b88f3cca661e',
            ],
        ]);

        $network = $task->getNetwork();

        self::assertInstanceOf(RemoteTasksNetwork::class, $network);
        self::assertSame([], $network->remoteTasks);
        self::assertSame('01a19dae-4677-7551-a573-b88f3cca661e', $network->networkVersion);
    }

    public function testFromArrayWithRemoteTasksNetworkEntries(): void
    {
        $task = Task::fromArray([
            'taskUid' => 862,
            'status' => 'succeeded',
            'type' => 'networkTopologyChange',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'remote_tasks' => [
                    'ms-01' => ['taskUid' => 863, 'error' => null],
                ],
            ],
        ]);

        self::assertEquals(
            new RemoteTasksNetwork(
                remoteTasks: ['ms-01' => new RemoteTask(taskUid: 863, error: null)],
                networkVersion: null,
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithRemoteTaskReplicationError(): void
    {
        $task = Task::fromArray([
            'taskUid' => 864,
            'indexUid' => 'movies',
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'remote_tasks' => [
                    'ms-01' => ['taskUid' => 865, 'error' => null],
                    'ms-02' => [
                        'error' => [
                            'message' => 'could not authenticate against the remote host',
                            'code' => 'remote_invalid_api_key',
                            'type' => 'auth',
                            'link' => 'https://docs.meilisearch.com/errors#remote_invalid_api_key',
                        ],
                    ],
                ],
                'network_version' => '01a19dae-4677-7551-a573-b88f3cca661e',
            ],
        ]);

        self::assertEquals(
            new RemoteTasksNetwork(
                remoteTasks: [
                    'ms-01' => new RemoteTask(taskUid: 865, error: null),
                    'ms-02' => new RemoteTask(
                        taskUid: null,
                        error: new TaskError(
                            'could not authenticate against the remote host',
                            'remote_invalid_api_key',
                            'auth',
                            'https://docs.meilisearch.com/errors#remote_invalid_api_key',
                        ),
                    ),
                ],
                networkVersion: '01a19dae-4677-7551-a573-b88f3cca661e',
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithOriginNetwork(): void
    {
        $task = Task::fromArray([
            'taskUid' => 863,
            'status' => 'succeeded',
            'type' => 'networkTopologyChange',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'origin' => [
                    'remoteName' => 'ms-00',
                    'taskUid' => 862,
                    'networkVersion' => '01a19daf-75f0-7c71-b28f-ffca8314f033',
                ],
            ],
        ]);

        self::assertEquals(
            new OriginNetwork(
                origin: new TaskOrigin(
                    remoteName: 'ms-00',
                    taskUid: 862,
                    networkVersion: '01a19daf-75f0-7c71-b28f-ffca8314f033',
                ),
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithOriginNetworkWithoutNetworkVersion(): void
    {
        $task = Task::fromArray([
            'taskUid' => 863,
            'status' => 'succeeded',
            'type' => 'networkTopologyChange',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'origin' => [
                    'remoteName' => 'ms-00',
                    'taskUid' => 862,
                ],
            ],
        ]);

        self::assertEquals(
            new OriginNetwork(
                origin: new TaskOrigin(remoteName: 'ms-00', taskUid: 862, networkVersion: null),
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithImportNetwork(): void
    {
        $task = Task::fromArray([
            'taskUid' => 6,
            'indexUid' => 'movies',
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'import_from' => [
                    'remoteName' => 'ms-01',
                    'indexName' => 'movies',
                    'documentCount' => 12,
                ],
                'network_change' => [
                    'remoteName' => 'ms-00',
                    'taskUid' => 5,
                    'networkVersion' => 'uuid',
                ],
            ],
        ]);

        self::assertEquals(
            new ImportNetwork(
                importFrom: new TaskImportData(remoteName: 'ms-01', indexName: 'movies', documentCount: 12),
                networkChange: new TaskOrigin(remoteName: 'ms-00', taskUid: 5, networkVersion: 'uuid'),
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithImportNetworkWithoutIndexName(): void
    {
        $task = Task::fromArray([
            'taskUid' => 6,
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => [
                'import_from' => [
                    'remoteName' => 'ms-01',
                    'indexName' => null,
                    'documentCount' => 0,
                ],
                'network_change' => [
                    'remoteName' => 'ms-00',
                    'taskUid' => 5,
                ],
            ],
        ]);

        self::assertEquals(
            new ImportNetwork(
                importFrom: new TaskImportData(remoteName: 'ms-01', indexName: null, documentCount: 0),
                networkChange: new TaskOrigin(remoteName: 'ms-00', taskUid: 5, networkVersion: null),
            ),
            $task->getNetwork(),
        );
    }

    public function testFromArrayWithUnknownNetwork(): void
    {
        $task = Task::fromArray([
            'taskUid' => 8,
            'status' => 'succeeded',
            'type' => 'documentAdditionOrUpdate',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'network' => ['futureKey' => ['x' => 1]],
        ]);

        self::assertEquals(new UnknownTaskNetwork(['futureKey' => ['x' => 1]]), $task->getNetwork());
    }

    public function testFromArrayWithoutNetwork(): void
    {
        $task = Task::fromArray([
            'taskUid' => 7,
            'status' => 'succeeded',
            'type' => 'taskCancelation',
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
        ]);

        self::assertNull($task->getNetwork());
    }

    #[DataProvider('emptyDetailsProvider')]
    public function testFromArrayWithEmptyDetailsReturnsNullDetails(string $type): void
    {
        $task = Task::fromArray([
            'taskUid' => 5,
            'status' => 'succeeded',
            'type' => $type,
            'enqueuedAt' => '2025-04-09T10:28:12.236789123Z',
            'details' => [],
        ]);

        self::assertNull($task->getDetails());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyDetailsProvider(): iterable
    {
        yield 'indexCreation' => ['indexCreation'];
        yield 'settingsUpdate' => ['settingsUpdate'];
    }
}
