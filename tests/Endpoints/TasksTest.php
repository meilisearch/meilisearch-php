<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\TaskCancelationDetails;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class TasksTest extends TestCase
{
    private Indexes $index;
    private string $indexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexName = $this->safeIndexName();
        $this->index = $this->createEmptyIndex($this->indexName);
    }

    public function testGetOneTaskFromWaitTask(): void
    {
        [$task, $completedTask] = $this->seedIndex();

        self::assertSame($completedTask->getTaskUid(), $task->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $completedTask->getType());
        self::assertSame($this->indexName, $completedTask->getIndexUid());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $completedTask->getDetails());
    }

    public function testGetOneTaskClient(): void
    {
        [$seedTask] = $this->seedIndex();

        $task = $this->index->getTask($seedTask->getTaskUid());
        self::assertSame($task->getTaskUid(), $seedTask->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $task->getType());
        self::assertSame($this->indexName, $task->getIndexUid());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $task->getDetails());
    }

    public function testGetAllTasksClient(): void
    {
        $tasks = $this->client->getTasks();
        $firstIndex = $tasks->getResults()[0]->getTaskUid();
        $this->seedIndex();

        $tasks = $this->client->getTasks();
        $newFirstIndex = $tasks->getResults()[0]->getTaskUid();

        self::assertNotSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksClientWithPagination(): void
    {
        $tasks = $this->client->getTasks((new TasksQuery())->setLimit(0));

        self::assertSame([], $tasks->getResults());
    }

    public function getAllTasksClientWithBatchFilter(): void
    {
        [$task] = $this->seedIndex();
        $task = $this->client->getTask($task->getTaskUid());

        $tasks = $this->client->getTasks(
            (new TasksQuery())
                ->setBatchUid($task->getTaskUid())
        );

        self::assertGreaterThan(0, $tasks->getTotal());
    }

    public function testGetAllTasksByIndex(): void
    {
        $response = $this->index->getTasks();
        $firstIndex = $response->getResults()[0]->getTaskUid();

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]->getTaskUid();

        self::assertSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksByIndexWithFilter(): void
    {
        $response = $this->index->getTasks((new TasksQuery())
            ->setAfterEnqueuedAt(new \DateTime('yesterday'))->setStatuses(['succeeded'])->setLimit(2));

        $firstIndex = $response->getResults()[0]->getTaskUid();
        self::assertSame(TaskStatus::Succeeded, $response->getResults()[0]->getStatus());

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]->getTaskUid();

        self::assertSame($firstIndex, $newFirstIndex);
        self::assertGreaterThan(0, $response->getTotal());
    }

    public function testCancelTasksWithFilter(): void
    {
        $date = new \DateTime('yesterday');
        $query = http_build_query(['afterEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
        $task = $this->client->cancelTasks((new CancelTasksQuery())->setAfterEnqueuedAt($date));

        $cancelTask = $this->client->waitForTask($task->getTaskUid());
        self::assertSame(TaskStatus::Succeeded, $cancelTask->getStatus());

        self::assertInstanceOf(TaskCancelationDetails::class, $details = $cancelTask->getDetails());
        self::assertSame('?'.$query, $details->originalFilter);
    }

    public function testGetAllTasksInReverseOrder(): void
    {
        $sampleTasks = $this->client->getTasks(new TasksQuery());

        $sampleTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $sampleTasks->getResults());

        $expectedTasks = $this->client->getTasks((new TasksQuery())->setUids($sampleTasksUids));
        $expectedTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $expectedTasks->getResults());

        $reversedTasks = $this->client->getTasks((new TasksQuery())->setUids($sampleTasksUids)->setReverse(true));
        $reversedTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $reversedTasks->getResults());

        self::assertSame(array_reverse($expectedTasksUids), $reversedTasksUids);
    }

    public function testExceptionIfNoTaskIdWhenGetting(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Task `99999999` not found.');

        $this->index->getTask(99999999);
    }

    /**
     * @return array{0: Task, 1: Task}
     */
    private function seedIndex(): array
    {
        $task = $this->index->updateDocuments(self::DOCUMENTS);
        $completedTask = $this->client->waitForTask($task->getTaskUid());

        return [$task, $completedTask];
    }
}
