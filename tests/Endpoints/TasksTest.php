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
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentPartial');
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
        $response = $this->client->getTask($promise['uid']);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentPartial');
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
        $preCount = \count($response['results']);
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($promise);
        $response = $this->client->getTasks();
        $this->assertCount($preCount + 1, $response['results']);
    }

    public function testGetOneTaskIndex(): void
    {
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($promise);
        $response = $this->index->getTask($promise['uid']);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentPartial');
        $this->assertArrayHasKey('indexUid', $response);
        $this->assertSame($response['indexUid'], 'index');
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
        $this->assertIsArray($response['details']);
    }

    public function testGetAllTasksIndex(): void
    {
        $response = $this->index->getTasks();
        $preCount = \count($response['results']);
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($promise);
        $response = $this->index->getTasks();
        $this->assertCount($preCount + 1, $response['results']);
    }

    public function testExceptionIfNoTaskIdWhenGetting(): void
    {
        $this->expectException(ApiException::class);
        $this->index->getTask(9999999999);
    }

    private function seedIndex(): array
    {
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $response = $this->client->waitForTask($promise['uid']);

        return [$promise, $response];
    }
}
