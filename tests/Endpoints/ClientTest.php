<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Client;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class ClientTest extends TestCase
{
    public function testClientIndexMethodsAlwaysReturnArray(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        /* @phpstan-ignore-next-line */
        $this->assertIsIterable($this->client->getIndexes());
        /* @phpstan-ignore-next-line */
        $this->assertIsArray($this->client->getRawIndex($index->getUid()));
    }

    public function testClientIndexMethodsAlwaysReturnsIndexesInstance(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(Indexes::class, $this->client->getIndex($index->getUid()));
        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(Indexes::class, $this->client->index($index->getUid()));
    }

    public function testgetIndexesWhenEmpty(): void
    {
        $response = $this->client->getIndexes();

        $this->assertEmpty($response);
    }

    public function testgetIndexesWithPagination(): void
    {
        $response = $this->client->getIndexes((new IndexesQuery())->setLimit(1)->setOffset(99999));

        $this->assertEmpty($response);
    }

    public function testExceptionIsThrownOnGetRawIndexWhenIndexDoesNotExist(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $this->client->getRawIndex('index');
    }

    public function testCreateIndexWithOnlyUid(): void
    {
        $indexName = $this->safeIndexName();
        $index = $this->createEmptyIndex($indexName);

        $this->assertSame($indexName, $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey(): void
    {
        $indexName = $this->safeIndexName();
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'ObjectId']
        );

        $this->assertSame($indexName, $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testCreateIndexWithUidInOptions(): void
    {
        $indexName = $this->safeIndexName();
        $index = $this->createEmptyIndex(
            $indexName,
            [
                'uid' => 'wrong',
                'primaryKey' => 'ObjectId',
            ]
        );

        $this->assertSame($indexName, $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testgetIndexes(): void
    {
        $booksIndex1 = $this->safeIndexName('books-1');
        $booksIndex2 = $this->safeIndexName('books-2');
        $response = $this->client->createIndex($booksIndex1);
        $this->client->waitForTask($response['taskUid']);
        $response = $this->client->createIndex($booksIndex2);
        $this->client->waitForTask($response['taskUid']);

        $indexes = $this->client->getIndexes();

        $this->assertCount(2, $indexes);
    }

    public function testGetRawIndex(): void
    {
        $this->createEmptyIndex('books-1');

        $res = $this->client->getRawIndex('books-1');

        $this->assertArrayHasKey('uid', $res);
    }

    public function testUpdateIndex(): void
    {
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex($indexName);

        $response = $this->client->updateIndex($indexName, ['primaryKey' => 'id']);
        $this->client->waitForTask($response['taskUid']);
        $index = $this->client->getIndex($response['indexUid']);

        $this->assertSame($index->getPrimaryKey(), 'id');
        $this->assertSame($index->getUid(), $indexName);
    }

    public function testDeleteIndex(): void
    {
        $this->createEmptyIndex($this->safeIndexName());

        $response = $this->client->getIndexes();
        $this->assertCount(1, $response);

        $response = $this->client->deleteIndex('index');
        $this->client->waitForTask($response['taskUid']);

        $this->expectException(ApiException::class);
        $index = $this->client->getIndex('index');

        $this->assertEmpty($index);
        $indexes = $this->client->getIndexes();

        $this->assertCount(0, $indexes);
    }

    public function testGetIndex(): void
    {
        $indexName = $this->safeIndexName();
        $this->createEmptyIndex($indexName);

        $index = $this->client->getIndex($indexName);
        $this->assertSame($indexName, $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testIndex(): void
    {
        $this->createEmptyIndex($this->safeIndexName());

        $index = $this->client->index('index');
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testExceptionIfUidIsNullWhenCreating(): void
    {
        $this->expectException(\TypeError::class);
        $this->createEmptyIndex(null);
    }

    public function testExceptionIfUidIsEmptyStringWhenCreating(): void
    {
        $this->expectException(ApiException::class);
        $this->createEmptyIndex('');
    }

    public function testExceptionIfNoIndexWhenShowing(): void
    {
        $this->expectException(ApiException::class);
        $this->client->getIndex('a-non-existing-index');
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
        $this->assertArrayHasKey('commitDate', $response);
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
        $this->expectException(\Exception::class);
        $client = new Client('http://127.0.0.1.com:1234', 'some-key');
        $client->createIndex('index');
    }

    public function testHeaderWithoutApiKey(): void
    {
        $client = new Client($this->host);

        $response = $client->health();

        $this->assertEquals('available', $response['status']);
        $this->expectException(ApiException::class);
        $response = $client->stats();
    }
}
