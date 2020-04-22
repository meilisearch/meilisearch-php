<?php

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
        $promise = $this->index->updateDocuments([
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ]);

        $this->index->waitForPendingUpdate($promise['updateId']);
    }
}
