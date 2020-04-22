<?php

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class SearchTest extends TestCase
{
    private $index;

    public function testBasicSearch()
    {
        $this->createFreshIndexAndSeedDocuments();

        $response = $this->index->search('prince');

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertCount(2, $response['hits']);
    }

    public function testSearchWithOptions()
    {
        $this->createFreshIndexAndSeedDocuments();

        $response = $this->index->search('prince', ['limit' => 1]);

        $this->assertCount(1, $response['hits']);
    }

    public function testBasicSearchIfNoPrimaryKeyAndDocumentProvided()
    {
        $emptyIndex = $this->client->createIndex('empty');

        $res = $emptyIndex->search('prince');

        $this->assertArrayHasKey('hits', $res);
        $this->assertArrayHasKey('offset', $res);
        $this->assertArrayHasKey('limit', $res);
        $this->assertArrayHasKey('processingTimeMs', $res);
        $this->assertArrayHasKey('query', $res);
        $this->assertCount(0, $res['hits']);
    }

    public function testExceptionIfNoIndexWhenSearching()
    {
        $index = $this->client->createIndex('another-index');
        $index->delete();

        $this->expectException(HTTPRequestException::class);

        $index->search('prince');
    }

    private function createFreshIndexAndSeedDocuments()
    {
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
        $promise = $this->index->updateDocuments(self::DOCUMENTS);

        $this->index->waitForPendingUpdate($promise['updateId']);
    }
}
