<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
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
            self::assertArrayHasKey($this->indexName, $result->getStats()->getIndexUids());
        }
    }

    public function testGetAllBatchesWithTasksFilters(): void
    {
        $tasks = $this->client->getTasks(new TasksQuery())->getResults();
        $response = $this->client->getBatches((new BatchesQuery())->setUids([$tasks[0]->getTaskUid()]));
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
        $batchUids = array_map(static fn ($b) => $b->getUid(), $batches->getResults());
        $reversedUids = array_map(static fn ($b) => $b->getUid(), $reversedBatches->getResults());
        self::assertSame($batchUids, array_reverse($reversedUids));
    }

    public function testGetOneBatch(): void
    {
        $batches = $this->client->getBatches();
        $first = $batches->getResults()[0];
        $response = $this->client->getBatch($first->getUid());

        self::assertSame($first->getUid(), $response->getUid());
        self::assertInstanceOf(UnknownTaskDetails::class, $response->getDetails());
        $stats = $response->getStats();
        self::assertSame($stats->getTotalNbTasks(), array_sum($stats->getStatus()));
        self::assertNotEmpty($stats->getStatus());
        self::assertNotEmpty($stats->getTypes());
        self::assertNotNull($stats->getProgressTrace());
        self::assertSame($response->toArray()['batchStrategy'] ?? null, $response->getBatchStrategy());
    }
}
