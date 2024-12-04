<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotNull;

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
    self::assertIsArray($response->getResults());
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
    self::assertNotNull($response->getResults());
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
    self::assertEquals($batches->getResults(), array_reverse($reversedBatches->getResults()));
  }

  public function testGetOneBatch(): void
  {
    $batches = $this->client->getBatches();
    $response = $this->client->getBatch($batches->getResults()[0]['uid']);

    self::assertEquals($batches->getResults()[0]['uid'], $response['uid']);
    self::assertArrayHasKey('details', $response);
    self::assertArrayHasKey('totalNbTasks', $response['stats']);
    self::assertArrayHasKey('status', $response['stats']);
    self::assertArrayHasKey('types', $response['stats']);
    self::assertArrayHasKey('indexUids', $response['stats']);
    self::assertArrayHasKey('duration', $response);
    self::assertArrayHasKey('startedAt', $response);
    self::assertArrayHasKey('finishedAt', $response);
  }
}
