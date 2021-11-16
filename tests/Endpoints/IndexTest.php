<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use DateTimeInterface;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
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

    public function testGetCreatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        $this->assertNull($indexB->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $this->index->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        $this->assertNull($indexB->getUpdatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $this->index->getUpdatedAt());
    }

    public function testGetCreatedAtString(): void
    {
        $indexB = $this->client->index('indexB');
        $rawInfo = $this->index->fetchRawInfo();

        $this->assertNull($indexB->getCreatedAtString());
        $this->assertSame($rawInfo['createdAt'], $this->index->getCreatedAtString());
    }

    public function testGetUpdatedAtString(): void
    {
        $indexB = $this->client->index('indexB');
        $rawInfo = $this->index->fetchRawInfo();

        $this->assertNull($indexB->getUpdatedAtString());
        $this->assertSame($rawInfo['updatedAt'], $this->index->getUpdatedAtString());
    }

    public function testfetchRawInfo(): void
    {
        $index = $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $response = $index->fetchRawInfo();

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

        $index = $this->index->update(['primaryKey' => $primaryKey]);

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame($index->getPrimaryKey(), $primaryKey);
        $this->assertSame($index->getUid(), 'index');
        $this->assertSame($this->index->getPrimaryKey(), $primaryKey);
        $this->assertSame($this->index->getUid(), 'index');
    }

    public function testIndexStats(): void
    {
        $stats = $this->index->stats();

        $this->assertArrayHasKey('numberOfDocuments', $stats);
        $this->assertEquals(0, $stats['numberOfDocuments']);
        $this->assertArrayHasKey('isIndexing', $stats);
        $this->assertArrayHasKey('fieldDistribution', $stats);
    }

    public function testFetchInfo(): void
    {
        $uid = 'indexA';
        $this->client->createIndex(
            $uid,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($uid);
        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertNull($index->getPrimaryKey());

        $index = $index->fetchInfo();
        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('objectID', $index->getPrimaryKey());
        $this->assertSame($uid, $index->getUid());
        $this->assertInstanceOf(DateTimeInterface::class, $index->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $index->getUpdatedAt());
    }

    public function testGetAndFetchPrimaryKey(): void
    {
        $uid = 'indexA';
        $this->client->createIndex(
            $uid,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($uid);
        $this->assertNull($index->getPrimaryKey());
        $this->assertSame('objectID', $index->fetchPrimaryKey());
        $this->assertSame('objectID', $index->getPrimaryKey());
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

    public function testDeleteIndexes(): void
    {
        $this->index = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB');

        $res = $this->index->delete();
        $this->assertEmpty($res);

        $res = $indexB->delete();
        $this->assertEmpty($res);
    }

    public function testParseDate(): void
    {
        $date = '2021-01-01T01:23:45.123456Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        $this->assertInstanceOf(DateTimeInterface::class, $dateTime);
        $this->assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWithExtraFractionalSeconds(): void
    {
        $date = '2021-01-01T01:23:45.123456789Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        $this->assertInstanceOf(DateTimeInterface::class, $dateTime);
        $this->assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWhenNull(): void
    {
        $dateTime = Indexes::parseDate(null);

        $this->assertNull($dateTime);
    }

    public function testExceptionIsThrownIfNoIndexWhenShowing(): void
    {
        $this->index->delete();

        $this->expectException(ApiException::class);

        $this->index->fetchInfo();
    }

    public function testExceptionIsThrownIfNoIndexWhenUpdating(): void
    {
        $this->index->delete();

        $this->expectException(ApiException::class);
        $this->index->update(['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownIfNoIndexWhenDeleting(): void
    {
        $this->index->delete();

        $this->expectException(ApiException::class);
        $this->index->delete();
    }
}
