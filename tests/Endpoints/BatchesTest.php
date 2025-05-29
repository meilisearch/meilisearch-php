<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Contracts\TasksQuery;
use Tests\TestCase;

final class BatchesTest extends TestCase
{
    private string $indexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexName = $this->safeIndexName();
        $this->createEmptyIndex($this->indexName);
    }

    public function testGetAllBatches(): void
    {
        $response = $this->client->getBatches();
        self::assertGreaterThan(0, $response->getTotal());
    }

    public function testGetAllBatchesWithIndexUidFilters(): void
    {
        $response = $this->client->getBatches((new BatchesQuery())->setIndexUids([$this->indexName]));
        foreach ($response->getResults() as $result) {
            self::assertArrayHasKey($this->indexName, $result['stats']['indexUids']);
        }
    }

    public function testGetAllBatchesWithTasksFilters(): void
    {
        $tasks = $this->client->getTasks(new TasksQuery())->getResults();
        $response = $this->client->getBatches((new BatchesQuery())->setUids([$tasks[0]['uid']]));
        self::assertGreaterThan(0, $response->getTotal());
    }

    public function testGetAllBatchesInReverseOrder(): void
    {
        $startDate = new \DateTimeImmutable('now');

        $batches = $this->client->getBatches((new BatchesQuery())
                ->setAfterEnqueuedAt($startDate)
        );
        $reversedBatches = $this->client->getBatches((new BatchesQuery())
                ->setAfterEnqueuedAt($startDate)
                ->setReverse(true)
        );
        self::assertSame($batches->getResults(), array_reverse($reversedBatches->getResults()));
    }

    public function testGetOneBatch(): void
    {
        $batches = $this->client->getBatches();
        $response = $this->client->getBatch($batches->getResults()[0]['uid']);

        self::assertSame($batches->getResults()[0]['uid'], $response['uid']);
        self::assertArrayHasKey('details', $response);
        self::assertArrayHasKey('totalNbTasks', $response['stats']);
        self::assertArrayHasKey('status', $response['stats']);
        self::assertArrayHasKey('types', $response['stats']);
        self::assertArrayHasKey('indexUids', $response['stats']);
        self::assertArrayHasKey('progressTrace', $response['stats']);
        self::assertArrayHasKey('duration', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
        self::assertArrayHasKey('progress', $response);
    }
}
