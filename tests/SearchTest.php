<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    private static $client;
    private static $index;
    private static $empty_index;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes(static::$client);
        static::$index = static::$client->createIndex('uid');
        static::$empty_index = static::$client->createIndex('uid_empty');
        $documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
        $res = static::$index->updateDocuments($documents);
        static::$index->waitForPendingUpdate($res['updateId']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testBasicSearch()
    {
        $res = static::$index->search('prince');
        $this->assertArrayHasKey('hits', $res);
        $this->assertArrayHasKey('offset', $res);
        $this->assertArrayHasKey('limit', $res);
        $this->assertArrayHasKey('processingTimeMs', $res);
        $this->assertArrayHasKey('query', $res);
        $this->assertCount(2, $res['hits']);
    }

    public function testSearchWithOptions()
    {
        $res = static::$index->search('prince', ['limit' => 1]);
        $this->assertCount(1, $res['hits']);
    }

    public function testBasicSearchIfNoPrimaryKeyAndDocumentProvided()
    {
        $res = static::$empty_index->search('prince');
        $this->assertArrayHasKey('hits', $res);
        $this->assertArrayHasKey('offset', $res);
        $this->assertArrayHasKey('limit', $res);
        $this->assertArrayHasKey('processingTimeMs', $res);
        $this->assertArrayHasKey('query', $res);
        $this->assertCount(0, $res['hits']);
    }

    public function testExceptionIfNoIndexWhenSearching()
    {
        static::$index->delete();
        $this->expectException(HTTPRequestException::class);
        static::$index->search('prince');
    }
}
