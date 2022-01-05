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
        $this->index = $this->createEmptyIndex('index');
    }

    public function testGetPrimaryKey(): void
    {
        $indexB = $this->createEmptyIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->assertNull($this->index->getPrimaryKey());
        $this->assertSame('objectId', $indexB->getPrimaryKey());
    }

    public function testGetUid(): void
    {
        $indexB = $this->createEmptyIndex(
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
        $index = $this->createEmptyIndex(
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

        $response = $this->index->update(['primaryKey' => $primaryKey]);
        $this->client->waitForTask($response['uid']);
        $index = $this->client->getIndex($response['indexUid']);

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame($index->getPrimaryKey(), $primaryKey);
        $this->assertSame($index->getUid(), 'index');
        $this->assertSame($index->getPrimaryKey(), $primaryKey);
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
        $this->createEmptyIndex(
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
        $this->createEmptyIndex(
            $uid,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($uid);
        $this->assertNull($index->getPrimaryKey());
        $this->assertSame('objectID', $index->fetchPrimaryKey());
        $this->assertSame('objectID', $index->getPrimaryKey());
    }

    public function testWaitForTaskDefault(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $response = $this->index->waitForTask($promise['uid']);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAddition');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeoutAndInterval(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['uid'], 100, 20);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAddition');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeout(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['uid'], 100);

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['uid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAddition');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
    }

    public function testExceptionWhenTaskTimeOut(): void
    {
        $res = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->expectException(TimeOutException::class);
        $this->index->waitForTask($res['uid'], 0, 20);
    }

    public function testDeleteIndexes(): void
    {
        $this->index = $this->createEmptyIndex('indexA');
        $indexB = $this->createEmptyIndex('indexB');

        $res = $this->index->delete();
        $this->assertSame($res['indexUid'], 'indexA');
        $this->assertArrayHasKey('type', $res);
        $this->assertSame($res['type'], 'indexDeletion');
        $this->assertArrayHasKey('enqueuedAt', $res);

        $res = $indexB->delete();
        $this->assertSame($res['indexUid'], 'indexB');
        $this->assertArrayHasKey('type', $res);
        $this->assertSame($res['type'], 'indexDeletion');
        $this->assertArrayHasKey('enqueuedAt', $res);
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
}
