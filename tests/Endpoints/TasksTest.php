<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\TasksQuery;
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
        [$promise, $response] = $this->seedIndex();

        self::assertIsArray($response);
        self::assertArrayHasKey('status', $response);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('indexUid', $response);
        self::assertSame($this->indexName, $response['indexUid']);
        self::assertArrayHasKey('enqueuedAt', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
        self::assertIsArray($response['details']);
    }

    public function testGetOneTaskClient(): void
    {
        [$promise, $response] = $this->seedIndex();

        self::assertIsArray($promise);
        $response = $this->client->getTask($promise['taskUid']);
        self::assertArrayHasKey('status', $response);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('indexUid', $response);
        self::assertSame($this->indexName, $response['indexUid']);
        self::assertArrayHasKey('enqueuedAt', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
        self::assertIsArray($response['details']);
    }

    public function testGetAllTasksClient(): void
    {
        $response = $this->client->getTasks();
        $firstIndex = $response->getResults()[0]['uid'];
        $this->seedIndex();

        $response = $this->client->getTasks();
        $newFirstIndex = $response->getResults()[0]['uid'];

        self::assertNotSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksClientWithPagination(): void
    {
        $response = $this->client->getTasks((new TasksQuery())->setLimit(0));

        self::assertSame([], $response->getResults());
    }

    public function getAllTasksClientWithBatchFilter(): void
    {
        [$promise, $response] = $this->seedIndex();
        $task = $this->client->getTask($promise['taskUid']);

        $response = $this->client->getTasks((new TasksQuery())
                ->setBatchUid($task['uid'])
        );

        self::assertIsArray($response->getResults());
    }

    public function testGetOneTaskIndex(): void
    {
        [$promise, $response] = $this->seedIndex();

        self::assertIsArray($promise);
        $response = $this->index->getTask($promise['taskUid']);
        self::assertArrayHasKey('status', $response);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('indexUid', $response);
        self::assertSame($this->indexName, $response['indexUid']);
        self::assertArrayHasKey('enqueuedAt', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
        self::assertIsArray($response['details']);
    }

    public function testGetAllTasksByIndex(): void
    {
        $response = $this->index->getTasks();
        $firstIndex = $response->getResults()[0]['uid'];

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]['uid'];

        self::assertSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksByIndexWithFilter(): void
    {
        $response = $this->index->getTasks((new TasksQuery())
            ->setAfterEnqueuedAt(new \DateTime('yesterday'))->setStatuses(['succeeded'])->setLimit(2));

        $firstIndex = $response->getResults()[0]['uid'];
        self::assertSame('succeeded', $response->getResults()[0]['status']);

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]['uid'];

        self::assertSame($firstIndex, $newFirstIndex);
        self::assertGreaterThan(0, $response->getTotal());
    }

    public function testCancelTasksWithFilter(): void
    {
        $date = new \DateTime('yesterday');
        $query = http_build_query(['afterEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
        $promise = $this->client->cancelTasks((new CancelTasksQuery())->setAfterEnqueuedAt($date));

        self::assertSame('taskCancelation', $promise['type']);
        $response = $this->client->waitForTask($promise['taskUid']);

        self::assertSame('?' . $query, $response['details']['originalFilter']);
        self::assertSame('taskCancelation', $response['type']);
        self::assertSame('succeeded', $response['status']);
    }

    public function testExceptionIfNoTaskIdWhenGetting(): void
    {
        $this->expectException(ApiException::class);
        $this->index->getTask(99999999);
    }

    private function seedIndex(): array
    {
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $response = $this->client->waitForTask($promise['taskUid']);

        return [$promise, $response];
    }
}
