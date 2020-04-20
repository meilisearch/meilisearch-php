<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

class UpdatesTest extends TestCase
{
    private static $index;
    private static $documents;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes($client);
        static::$index = $client->createIndex('uid');
        static::$documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$index->delete();
    }

    public function testGetOneUpdate()
    {
        $update_id = static::$index->updateDocuments(static::$documents)['updateId'];
        $res = static::$index->waitForPendingUpdate($update_id);
        $this->assertIsArray($res);
        $this->assertSame($res['status'], 'processed');
        $this->assertSame($res['updateId'], $update_id);
        $this->assertArrayHasKey('type', $res);
        $this->assertIsArray($res['type']);
        $this->assertArrayHasKey('duration', $res);
        $this->assertArrayHasKey('enqueuedAt', $res);
        $this->assertArrayHasKey('processedAt', $res);
    }

    public function testGetAllUpdates()
    {
        $res = static::$index->getAllUpdateStatus();
        $this->assertCount(1, $res);
        $this->assertSame($res[0]['status'], 'processed');
        $this->assertArrayHasKey('updateId', $res[0]);
        $this->assertArrayHasKey('type', $res[0]);
        $this->assertIsArray($res[0]['type']);
        $this->assertArrayHasKey('duration', $res[0]);
        $this->assertArrayHasKey('enqueuedAt', $res[0]);
        $this->assertArrayHasKey('processedAt', $res[0]);
    }

    public function testExceptionIfNoUpdateIdWhenGetting()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->getUpdateStatus(10000);
    }
}
