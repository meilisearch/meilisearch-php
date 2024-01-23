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
        self::assertIsIterable($this->client->getIndexes());
        /* @phpstan-ignore-next-line */
        self::assertIsArray($this->client->getRawIndex($index->getUid()));
    }

    public function testClientIndexMethodsAlwaysReturnsIndexesInstance(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(Indexes::class, $this->client->getIndex($index->getUid()));
        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(Indexes::class, $this->client->index($index->getUid()));
    }

    public function testgetIndexesWhenEmpty(): void
    {
        $response = $this->client->getIndexes();

        self::assertEmpty($response);
    }

    public function testgetIndexesWithPagination(): void
    {
        $response = $this->client->getIndexes((new IndexesQuery())->setLimit(1)->setOffset(99999));

        self::assertEmpty($response);
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

        self::assertSame($indexName, $index->getUid());
        self::assertNull($index->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey(): void
    {
        $indexName = $this->safeIndexName();
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'ObjectId']
        );

        self::assertSame($indexName, $index->getUid());
        self::assertSame('ObjectId', $index->getPrimaryKey());
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

        self::assertSame($indexName, $index->getUid());
        self::assertSame('ObjectId', $index->getPrimaryKey());
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

        self::assertCount(2, $indexes);
    }

    public function testGetRawIndex(): void
    {
        $this->createEmptyIndex('books-1');

        $res = $this->client->getRawIndex('books-1');

        self::assertArrayHasKey('uid', $res);
    }

    public function testUpdateIndex(): void
    {
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex($indexName);

        $response = $this->client->updateIndex($indexName, ['primaryKey' => 'id']);
        $this->client->waitForTask($response['taskUid']);
        $index = $this->client->getIndex($response['indexUid']);

        self::assertSame('id', $index->getPrimaryKey());
        self::assertSame($indexName, $index->getUid());
    }

    public function testDeleteIndex(): void
    {
        $this->createEmptyIndex($this->safeIndexName());

        $response = $this->client->getIndexes();
        self::assertCount(1, $response);

        $response = $this->client->deleteIndex('index');
        $this->client->waitForTask($response['taskUid']);

        $this->expectException(ApiException::class);
        $index = $this->client->getIndex('index');

        self::assertEmpty($index);
        $indexes = $this->client->getIndexes();

        self::assertCount(0, $indexes);
    }

    public function testGetIndex(): void
    {
        $indexName = $this->safeIndexName();
        $this->createEmptyIndex($indexName);

        $index = $this->client->getIndex($indexName);
        self::assertSame($indexName, $index->getUid());
        self::assertNull($index->getPrimaryKey());
    }

    public function testIndex(): void
    {
        $this->createEmptyIndex($this->safeIndexName());

        $index = $this->client->index('index');
        self::assertSame('index', $index->getUid());
        self::assertNull($index->getPrimaryKey());
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

        self::assertSame('available', $response['status']);
    }

    public function testIsHealthyIsTrue(): void
    {
        $response = $this->client->isHealthy();

        self::assertTrue($response);
    }

    public function testIsHealthyIsFalse(): void
    {
        $client = new Client('http://127.0.0.1.com:1234', 'masterKey');
        $response = $client->isHealthy();

        self::assertFalse($response);
    }

    public function testVersion(): void
    {
        $response = $this->client->version();

        self::assertArrayHasKey('commitSha', $response);
        self::assertArrayHasKey('commitDate', $response);
        self::assertArrayHasKey('pkgVersion', $response);
    }

    public function testStats(): void
    {
        $response = $this->client->stats();

        self::assertArrayHasKey('databaseSize', $response);
        self::assertArrayHasKey('lastUpdate', $response);
        self::assertArrayHasKey('indexes', $response);
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

        self::assertSame('available', $response['status']);
        $this->expectException(ApiException::class);
        $response = $client->stats();
    }
}
