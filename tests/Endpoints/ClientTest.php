<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class ClientTest extends TestCase
{
    public function testGetAllIndexesWhenEmpty(): void
    {
        $response = $this->client->getAllIndexes();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testCreateIndexWithOnlyUid(): void
    {
        $index = $this->client->createIndex('index');

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey(): void
    {
        $index = $this->client->createIndex(
            'index',
            ['primaryKey' => 'ObjectId']
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testCreateIndexWithUidInOptions(): void
    {
        $index = $this->client->createIndex(
            'index',
            [
                'uid' => 'wrong',
                'primaryKey' => 'ObjectId',
            ]
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetAllIndexes(): void
    {
        $indexA = 'indexA';
        $indexB = 'indexB';
        $this->client->createIndex($indexA);
        $this->client->createIndex($indexB);

        $response = $this->client->getAllIndexes();

        $this->assertIsArray($response);
        $this->assertCount(2, $response);

        $uids = array_map(function ($index): ?string {
            return $index->getUid();
        }, $response);

        $this->assertContains($indexA, $uids);
        $this->assertContains($indexB, $uids);
    }

    public function testUpdateIndex(): void
    {
        $this->client->createIndex('indexA');

        $index = $this->client->updateIndex('indexA', ['primaryKey' => 'id']);

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame($index->getPrimaryKey(), 'id');
        $this->assertSame($index->getUid(), 'indexA');
    }

    public function testDeleteIndex(): void
    {
        $this->client->createIndex('index');

        $response = $this->client->getAllIndexes();
        $this->assertCount(1, $response);

        $response = $this->client->deleteIndex('index');

        $this->assertEmpty($response);
        $response = $this->client->getAllIndexes();

        $this->assertCount(0, $response);
    }

    public function testDeleteAllIndexes(): void
    {
        $this->client->createIndex('index-1');
        $this->client->createIndex('index-2');

        $response = $this->client->getAllIndexes();

        $this->assertCount(2, $response);

        $this->client->deleteAllIndexes();
        $response = $this->client->getAllIndexes();

        $this->assertCount(0, $response);
    }

    public function testDeleteAllIndexesWhenThereAreNoIndexes(): void
    {
        $response = $this->client->getAllIndexes();
        $this->assertCount(0, $response);

        $this->client->deleteAllIndexes();

        $this->assertCount(0, $response);
    }

    public function testGetIndex(): void
    {
        $this->client->createIndex('index');

        $index = $this->client->getIndex('index');
        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testIndex(): void
    {
        $this->client->createIndex('index');

        $index = $this->client->index('index');
        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithOnlyUid(): void
    {
        $index = $this->client->getOrCreateIndex('index');

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithUidAndPrimaryKey(): void
    {
        $index = $this->client->getOrCreateIndex(
            'index',
            ['primaryKey' => 'ObjectId']
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithUidInOptions(): void
    {
        $index = $this->client->getOrCreateIndex(
            'index',
            [
                'uid' => 'wrong',
                'primaryKey' => 'ObjectId',
            ]
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetOrCreateWithIndexAlreadyExists(): void
    {
        $index1 = $this->client->getOrCreateIndex('index');
        $index2 = $this->client->getOrCreateIndex('index');
        $index3 = $this->client->getOrCreateIndex('index');

        $this->assertSame('index', $index1->getUid());
        $this->assertSame('index', $index2->getUid());
        $this->assertSame('index', $index3->getUid());

        $update = $index1->addDocuments([['book_id' => 1, 'name' => 'Some book']]);
        $index1->waitForPendingUpdate($update['updateId']);

        $documents = $index2->getDocuments();
        $this->assertCount(1, $documents);
        $index2->delete();
    }

    public function testExceptionIsThrownWhenOverwritingPrimaryKeyUsingUpdateIndex(): void
    {
        $this->client->createIndex(
            'indexB',
            ['primaryKey' => 'objectId']
        );

        $this->expectException(ApiException::class);

        $this->client->updateIndex('indexB', ['primaryKey' => 'objectID']);
    }

    public function testExceptionIsThrownWhenUpdateIndexUseANoneExistingIndex(): void
    {
        $this->expectException(ApiException::class);

        $this->client->updateIndex(
            'IndexNotExist',
            ['primaryKey' => 'objectId']
        );
    }

    public function testExceptionIfUidTakenWhenCreating(): void
    {
        $this->client->createIndex('index');

        $this->expectException(ApiException::class);

        $this->client->createIndex('index');
    }

    public function testExceptionIfUidIsNullWhenCreating(): void
    {
        $this->expectException(\TypeError::class);
        $this->client->createIndex(null);
    }

    public function testExceptionIfUidIsEmptyStringWhenCreating(): void
    {
        $this->expectException(ApiException::class);
        $this->client->createIndex('');
    }

    public function testExceptionIfNoIndexWhenShowing(): void
    {
        $this->expectException(ApiException::class);
        $this->client->getIndex('a-non-existing-index');
    }

    public function testExceptionIfNoIndexWhenDeleting(): void
    {
        $this->expectException(ApiException::class);
        $this->client->deleteIndex('a-non-existing-index');
    }

    public function testHealth(): void
    {
        $response = $this->client->health();

        $this->assertEquals('available', $response['status']);
    }

    public function testIsHealthyIsTrue(): void
    {
        $response = $this->client->isHealthy();

        $this->assertTrue($response);
    }

    public function testIsHealthyIsFalse(): void
    {
        $client = new Client('http://127.0.0.1.com:1234', 'masterKey');
        $response = $client->isHealthy();

        $this->assertFalse($response);
    }

    public function testVersion(): void
    {
        $response = $this->client->version();

        $this->assertArrayHasKey('commitSha', $response);
        $this->assertArrayHasKey('buildDate', $response);
        $this->assertArrayHasKey('pkgVersion', $response);
    }

    public function testStats(): void
    {
        $response = $this->client->stats();

        $this->assertArrayHasKey('databaseSize', $response);
        $this->assertArrayHasKey('lastUpdate', $response);
        $this->assertArrayHasKey('indexes', $response);
    }

    public function testBadClientUrl(): void
    {
        try {
            $client = new Client('http://127.0.0.1.com:1234', 'some-key');
            $client->createIndex('index');
        } catch (\Exception $e) {
            $this->assertIsString($e->getMessage());

            return;
        }
        $this->fail('Bad client was accepted and the exception was not thrown');
    }
}
