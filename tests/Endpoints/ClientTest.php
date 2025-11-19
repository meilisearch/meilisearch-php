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

    public function testGetIndexes(): void
    {
        $booksIndex1 = $this->safeIndexName('books-1');
        $booksIndex2 = $this->safeIndexName('books-2');

        $this->client->createIndex($booksIndex1)->wait();
        $this->client->createIndex($booksIndex2)->wait();

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

        $task = $this->client->updateIndex($indexName, ['primaryKey' => 'id'])->wait();
        $index = $this->client->getIndex($task->getIndexUid());

        self::assertSame('id', $index->getPrimaryKey());
        self::assertSame($indexName, $index->getUid());
    }

    public function testDeleteIndex(): void
    {
        $this->createEmptyIndex($this->safeIndexName());

        $response = $this->client->getIndexes();
        self::assertCount(1, $response);

        $this->client->deleteIndex('index')->wait();

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

        self::assertMatchesRegularExpression('/^[0-9a-f]{40}$/i', $response->getCommitSha());
        self::assertGreaterThanOrEqual(0, version_compare($response->getPkgVersion(), '1.26.0'));
    }

    public function testStats(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('stats'));
        $index->addDocuments([
            ['objectID' => 1, 'type' => 'Library'],
            ['objectID' => 2],
        ])->wait();

        $response = $this->client->stats();

        self::assertGreaterThanOrEqual(0, $response->getDatabaseSize());
        self::assertGreaterThanOrEqual(0, $response->getUsedDatabaseSize());

        $statsIndex = $response->getIndexes()[$index->getUid()];

        self::assertSame(2, $statsIndex->getNumberOfDocuments());
        self::assertSame(['objectID' => 2, 'type' => 1], $statsIndex->getFieldDistribution());
    }

    public function testBadClientUrl(): void
    {
        $client = new Client('http://127.0.0.1.com:1234', 'some-key');

        $this->expectException(\Exception::class);

        $client->createIndex('index');
    }

    public function testHeaderWithoutApiKey(): void
    {
        $client = new Client($this->host);
        $response = $client->health();

        self::assertSame('available', $response['status']);

        $this->expectException(ApiException::class);

        $client->stats();
    }
}
