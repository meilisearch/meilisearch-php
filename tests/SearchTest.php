<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

require_once 'utils.php';

class SearchTest extends TestCase
{
    private static $index;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $client = new Client('http://localhost:7700', 'apiKey');
        deleteAllIndexes($client);
        static::$index = $client->createIndex('Name');
        $documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
        static::$index->addOrUpdateDocuments($documents);
        usleep(10 * 1000);
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

    public function testExceptionIfNoIndexWhenSearching()
    {
        static::$index->delete();
        $this->expectException(HTTPRequestException::class);
        static::$index->search('nope');
    }
}
