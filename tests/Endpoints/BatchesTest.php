<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class BatchesTest extends TestCase
{
  private Indexes $index;
  private string $indexName;

  protected function setUp(): void
  {
    parent::setUp();
    $this->indexName = $this->safeIndexName();
    $this->index = $this->createEmptyIndex($this->indexName);
  }

  public function testGetAllBatches(): void
  {
    $response = $this->client->getBatches();
    self::assertIsArray($response->getResults());
  }
}
