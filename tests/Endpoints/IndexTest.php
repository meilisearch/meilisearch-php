<?php

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Exceptions\TimeOutException;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private $index;

    public function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetPrimaryKey()
    {
        $indexB = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->assertNull($this->index->getPrimaryKey());
        $this->assertSame('objectId', $indexB->getPrimaryKey());
    }

    public function testGetUid()
    {
        $indexB = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );
        $this->assertSame('index', $this->index->getUid());
        $this->assertSame('indexB', $indexB->getUid());
    }

    public function testShow()
    {
        $index = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

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
        $primaryKey = 'id';

        $response = $this->index->update(['primaryKey' => $primaryKey]);

        $this->assertSame($response['primaryKey'], $primaryKey);
        $this->assertSame('index', $response['uid']);
    }

    public function testExceptionIsThrownWhenOverwritingPrimaryKey()
    {
        $index = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->expectException(HTTPRequestException::class);

        $index->update(['primaryKey' => 'objectID']);
    }

    public function testIndexStats()
    {
        $stats = $this->index->stats();

        $this->assertArrayHasKey('numberOfDocuments', $stats);
        $this->assertEquals(0, $stats['numberOfDocuments']);
        $this->assertArrayHasKey('isIndexing', $stats);
        $this->assertArrayHasKey('fieldsDistribution', $stats);
    }

    public function testWaitForPendingUpdateDefault()
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $response = $this->index->waitForPendingUpdate($promise['updateId']);

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
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForPendingUpdate($promise['updateId'], 100, 20);

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
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForPendingUpdate($promise['updateId'], 100);

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
        $this->expectException(TimeOutException::class);
        $res = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->index->waitForPendingUpdate($res['updateId'], 0, 20);
    }

    public function testDeleteIndexes()
    {
        $this->index = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB');

        $res = $this->index->delete();
        $this->assertEmpty($res);

        $res = $indexB->delete();
        $this->assertEmpty($res);
    }

    public function testExceptionIsThrownIfNoIndexWhenShowing()
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);

        $this->index->show();
    }

    public function testExceptionIsThrownIfNoIndexWhenUpdating()
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);
        $this->index->update(['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownIfNoIndexWhenDeleting()
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);
        $this->index->delete();
    }
}
