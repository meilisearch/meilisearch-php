<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\SimilarDocumentsQuery;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class SimilarDocumentsTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::VECTOR_MOVIES);
    }

    public function testBasicSearchWithSimilarDocuments(): void
    {
        $task = $this->index->updateSettings(['embedders' => ['manual' => ['source' => 'userProvided', 'dimensions' => 3]]]);
        $this->client->waitForTask($task['taskUid']);

        $response = $this->index->search('room');

        self::assertSame(1, $response->getHitsCount());

        $documentId = $response->getHit(0)['id'];
        $response = $this->index->searchSimilarDocuments(new SimilarDocumentsQuery($documentId));

        self::assertGreaterThanOrEqual(4, $response->getHitsCount());
        self::assertArrayNotHasKey('_vectors', $response->getHit(0));
        self::assertArrayHasKey('id', $response->getHit(0));
        self::assertSame($documentId, $response->getId());

        $similarQuery = new SimilarDocumentsQuery($documentId);
        $response = $this->index->searchSimilarDocuments($similarQuery->setRetrieveVectors(true));
        self::assertGreaterThanOrEqual(4, $response->getHitsCount());
        self::assertArrayHasKey('_vectors', $response->getHit(0));
        self::assertArrayHasKey('id', $response->getHit(0));
        self::assertSame($documentId, $response->getId());
    }
}
