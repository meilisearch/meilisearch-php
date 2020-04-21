<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Exceptions\TimeOutException;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetPrimaryKey()
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex([
            'uid' => 'indexB',
            'primaryKey' => 'objectId',
        ]);

        $this->assertNull($indexA->getPrimaryKey());
        $this->assertSame('objectId', $indexB->getPrimaryKey());
    }

    public function testGetUid()
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex([
            'uid' => 'indexB',
            'primaryKey' => 'objectId',
        ]);
        $this->assertSame('indexA', $indexA->getUid());
        $this->assertSame('indexB', $indexB->getUid());
    }

    public function testShow()
    {
        $index = $this->client->createIndex([
            'uid' => 'indexB',
            'primaryKey' => 'objectId',
        ]);

        $response = $index->show();

        $this->assertArrayHasKey('primaryKey', $response);
        $this->assertArrayHasKey('uid', $response);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);
        $this->assertSame($response['primaryKey'], 'objectId');
        $this->assertSame($response['uid'], 'indexB');
    }

    public function testPrimaryKeyUpdate()
    {
        $index = $this->client->createIndex('index');
        $primaryKey = 'id';

        $response = $index->update(['primaryKey' => $primaryKey]);

        $this->assertSame($response['primaryKey'], $primaryKey);
        $this->assertSame('index', $response['uid']);
    }

    public function testExceptionIfPrimaryKeyIsPresentWhenUpdating()
    {
        $index = $this->client->createIndex([
            'uid' => 'indexB',
            'primaryKey' => 'objectId',
        ]);

        $this->expectException(HTTPRequestException::class);

        $index->update(['primaryKey' => 'objectID']);
    }

    public function testIndexStats()
    {
        $index = $this->client->createIndex('index');

        $stats = $index->stats();

        $this->assertArrayHasKey('numberOfDocuments', $stats);
        $this->assertEquals(0, $stats['numberOfDocuments']);
        $this->assertArrayHasKey('isIndexing', $stats);
        $this->assertArrayHasKey('fieldsFrequency', $stats);
    }

    public function testWaitForPendingUpdateDefault()
    {
        $index = $this->client->createIndex('index');
        $promise = $index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $response = $index->waitForPendingUpdate($promise['updateId']);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'processed');
        $this->assertSame($response['updateId'], $promise['updateId']);
        $this->assertArrayHasKey('type', $response);
        $this->assertIsArray($response['type']);
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('processedAt', $response);
    }

    public function testWaitForPendingUpdateWithTimeoutAndInterval()
    {
        $index = $this->client->createIndex('index');

        $promise = $index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $index->waitForPendingUpdate($promise['updateId'], 100, 20);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'processed');
        $this->assertSame($response['updateId'], $promise['updateId']);
        $this->assertArrayHasKey('type', $response);
        $this->assertIsArray($response['type']);
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('processedAt', $response);
    }

    public function testWaitForPendingUpdateWithTimeout()
    {
        $index = $this->client->createIndex('index');

        $promise = $index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $index->waitForPendingUpdate($promise['updateId'], 100);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'processed');
        $this->assertSame($response['updateId'], $promise['updateId']);
        $this->assertArrayHasKey('type', $response);
        $this->assertIsArray($response['type']);
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('processedAt', $response);
    }

    public function testExceptionWhenPendingUpdateTimeOut()
    {
        $index = $this->client->createIndex('index');
        $this->expectException(TimeOutException::class);
        $res = $index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $index->waitForPendingUpdate($res['updateId'], 0, 20);
    }

    public function testDeleteIndexes()
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB');

        $res = $indexA->delete();
        $this->assertEmpty($res);

        $res = $indexB->delete();
        $this->assertEmpty($res);
    }

    public function testExceptionIsThrownIfNoIndexWhenShowing()
    {
        $index = $this->client->createIndex('index');
        $index->delete();

        $this->expectException(HTTPRequestException::class);

        $index->show();
    }

    public function testExceptionIsThrownIfNoIndexWhenUpdating()
    {
        $index = $this->client->createIndex('index');
        $index->delete();

        $this->expectException(HTTPRequestException::class);
        $index->update(['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownIfNoIndexWhenDeleting()
    {
        $index = $this->client->createIndex('index');
        $index->delete();

        $this->expectException(HTTPRequestException::class);
        $index->delete();
    }
}
