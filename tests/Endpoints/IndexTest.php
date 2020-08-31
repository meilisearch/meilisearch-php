<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Exceptions\TimeOutException;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetPrimaryKey(): void
    {
        $indexB = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->assertNull($this->index->getPrimaryKey());
        $this->assertSame('objectId', $indexB->getPrimaryKey());
    }

    public function testGetUid(): void
    {
        $indexB = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );
        $this->assertSame('index', $this->index->getUid());
        $this->assertSame('indexB', $indexB->getUid());
    }

    public function testShow(): void
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

    public function testPrimaryKeyUpdate(): void
    {
        $primaryKey = 'id';

        $response = $this->index->update(['primaryKey' => $primaryKey]);

        $this->assertSame($response['primaryKey'], $primaryKey);
        $this->assertSame('index', $response['uid']);
    }

    public function testExceptionIsThrownWhenOverwritingPrimaryKey(): void
    {
        $index = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->expectException(HTTPRequestException::class);

        $index->update(['primaryKey' => 'objectID']);
    }

    public function testIndexStats(): void
    {
        $stats = $this->index->stats();

        $this->assertArrayHasKey('numberOfDocuments', $stats);
        $this->assertEquals(0, $stats['numberOfDocuments']);
        $this->assertArrayHasKey('isIndexing', $stats);
        $this->assertArrayHasKey('fieldsDistribution', $stats);
    }

    public function testWaitForPendingUpdateDefault(): void
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

    public function testWaitForPendingUpdateWithTimeoutAndInterval(): void
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

    public function testWaitForPendingUpdateWithTimeout(): void
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

    public function testExceptionWhenPendingUpdateTimeOut(): void
    {
        $this->expectException(TimeOutException::class);
        $res = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->index->waitForPendingUpdate($res['updateId'], 0, 20);
    }

    public function testUpdateIndex(): void
    {
        $this->client->createIndex('indexA');

        $response = $this->client->updateIndex('indexA', ['primaryKey' => 'id']);

        $this->assertSame($response['primaryKey'], 'id');
        $this->assertSame($response['uid'], 'indexA');
    }

    public function testExceptionIsThrownWhenOverwritingPrimaryKeyUsingUpdateIndex(): void
    {
        $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->expectException(HTTPRequestException::class);

        $this->client->updateIndex('indexB', ['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownWhenUpdateIndexUseAnNoneExistingIndex(): void
    {
        $this->expectException(HTTPRequestException::class);

        $this->client->updateIndex(
            'IndexNotExist',
            ['primaryKey' => 'objectId']
        );
    }

    public function testDeleteIndexes(): void
    {
        $this->index = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB');

        $res = $this->index->delete();
        $this->assertEmpty($res);

        $res = $indexB->delete();
        $this->assertEmpty($res);
    }

    public function testExceptionIsThrownIfNoIndexWhenShowing(): void
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);

        $this->index->show();
    }

    public function testExceptionIsThrownIfNoIndexWhenUpdating(): void
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);
        $this->index->update(['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownIfNoIndexWhenDeleting(): void
    {
        $this->index->delete();

        $this->expectException(HTTPRequestException::class);
        $this->index->delete();
    }
}
