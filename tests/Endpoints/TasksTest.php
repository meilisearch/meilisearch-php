<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class TasksTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex('index');
    }

    public function testGetOneTaskFromWaitTask(): void
    {
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('indexUid', $response);
        $this->assertSame($response['indexUid'], 'index');
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
        $this->assertIsArray($response['details']);
    }

    public function testGetOneTaskClient(): void
    {
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($promise);
        $response = $this->client->getTask($promise['taskUid']);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('indexUid', $response);
        $this->assertSame($response['indexUid'], 'index');
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
        $this->assertIsArray($response['details']);
    }

    public function testGetAllTasksClient(): void
    {
        $response = $this->client->getTasks();
        $firstIndex = $response['results'][0]['uid'];
        $this->seedIndex();

        $response = $this->client->getTasks();
        $newFirstIndex = $response['results'][0]['uid'];

        $this->assertNotEquals($firstIndex, $newFirstIndex);
    }

    public function testGetOneTaskIndex(): void
    {
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($promise);
        $response = $this->index->getTask($promise['taskUid']);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('indexUid', $response);
        $this->assertSame($response['indexUid'], 'index');
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
        $this->assertIsArray($response['details']);
    }

    public function testGetAllTasksByIndex(): void
    {
        $response = $this->index->getTasks();
        $firstIndex = $response['results'][0]['uid'];

        $newIndex = $this->createEmptyIndex('a_new_index');
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response['results'][0]['uid'];

        $this->assertEquals($firstIndex, $newFirstIndex);
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
