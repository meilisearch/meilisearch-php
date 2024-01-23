<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\TimeOutException;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    private Indexes $index;
    private string $indexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexName = $this->safeIndexName();
        $this->index = $this->createEmptyIndex($this->indexName);
    }

    public function testIndexGetSettings(): void
    {
        self::assertSame([], $this->index->getSynonyms());
        self::assertSame([], $this->index->getStopWords());
        self::assertSame([], $this->index->getSortableAttributes());
        self::assertSame(['*'], $this->index->getSearchableAttributes());
        self::assertSame(
            ['words', 'typo', 'proximity', 'attribute', 'sort', 'exactness'],
            $this->index->getRankingRules()
        );
        self::assertSame([], $this->index->getFilterableAttributes());
        self::assertSame(['*'], $this->index->getDisplayedAttributes());
        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => [
                '*' => 'alpha',
            ],
        ], $this->index->getFaceting());
        self::assertSame(['maxTotalHits' => 1000], $this->index->getPagination());
        self::assertSame(
            [
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 5, 'twoTypos' => 9],
                'disableOnWords' => [],
                'disableOnAttributes' => [],
            ],
            $this->index->getTypoTolerance(),
        );
        self::assertSame([], $this->index->getDictionary());
    }

    public function testGetPrimaryKey(): void
    {
        $index = $this->createEmptyIndex(
            $this->safeIndexName('books-2'),
            ['primaryKey' => 'objectId']
        );

        self::assertNull($this->index->getPrimaryKey());
        self::assertSame('objectId', $index->getPrimaryKey());
    }

    public function testGetUid(): void
    {
        $indexName = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectId']
        );
        self::assertSame($this->indexName, $this->index->getUid());
        self::assertSame($indexName, $index->getUid());
    }

    public function testGetCreatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        self::assertNull($indexB->getCreatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $this->index->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        self::assertNull($indexB->getUpdatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $this->index->getUpdatedAt());
    }

    public function testFetchRawInfo(): void
    {
        $indexName = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectId']
        );

        $response = $index->fetchRawInfo();

        self::assertArrayHasKey('primaryKey', $response);
        self::assertArrayHasKey('uid', $response);
        self::assertArrayHasKey('createdAt', $response);
        self::assertArrayHasKey('updatedAt', $response);
        self::assertSame('objectId', $response['primaryKey']);
        self::assertSame($indexName, $response['uid']);
    }

    public function testPrimaryKeyUpdate(): void
    {
        $primaryKey = 'id';

        $response = $this->index->update(['primaryKey' => $primaryKey]);
        $this->client->waitForTask($response['taskUid']);
        $index = $this->client->getIndex($response['indexUid']);

        self::assertSame($primaryKey, $index->getPrimaryKey());
        self::assertSame($this->indexName, $index->getUid());
        self::assertSame($this->indexName, $this->index->getUid());
    }

    public function testIndexStats(): void
    {
        $stats = $this->index->stats();

        self::assertArrayHasKey('numberOfDocuments', $stats);
        self::assertSame(0, $stats['numberOfDocuments']);
        self::assertArrayHasKey('isIndexing', $stats);
        self::assertArrayHasKey('fieldDistribution', $stats);
    }

    public function testFetchInfo(): void
    {
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($indexName);
        self::assertNull($index->getPrimaryKey());

        $index = $index->fetchInfo();
        self::assertSame('objectID', $index->getPrimaryKey());
        self::assertSame($indexName, $index->getUid());
        self::assertInstanceOf(\DateTimeInterface::class, $index->getCreatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $index->getUpdatedAt());
    }

    public function testGetAndFetchPrimaryKey(): void
    {
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($indexName);
        self::assertNull($index->getPrimaryKey());
        self::assertSame('objectID', $index->fetchPrimaryKey());
        self::assertSame('objectID', $index->getPrimaryKey());
    }

    public function testGetTasks(): void
    {
        $promise = $this->client->createIndex('new-index', ['primaryKey' => 'objectID']);
        $this->index->waitForTask($promise['taskUid']);
        $promise = $this->client->createIndex('other-index', ['primaryKey' => 'objectID']);
        $this->index->waitForTask($promise['taskUid']);
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->index->waitForTask($promise['taskUid']);

        $tasks = $this->index->getTasks((new TasksQuery())->setIndexUids(['other-index']));

        $allIndexUids = array_map(function ($val) { return $val['indexUid']; }, $tasks->getResults());
        $results = array_unique($allIndexUids);
        $expected = [$this->index->getUid(), 'other-index'];

        self::assertSame($expected, $results);
    }

    public function testWaitForTaskDefault(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $response = $this->index->waitForTask($promise['taskUid']);

        /* @phpstan-ignore-next-line */
        self::assertIsArray($response);
        self::assertSame('succeeded', $response['status']);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('duration', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeoutAndInterval(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['taskUid'], 100, 20);

        self::assertSame('succeeded', $response['status']);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('duration', $response);
        self::assertArrayHasKey('enqueuedAt', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeout(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['taskUid'], 100);

        self::assertSame('succeeded', $response['status']);
        self::assertSame($response['uid'], $promise['taskUid']);
        self::assertArrayHasKey('type', $response);
        self::assertSame('documentAdditionOrUpdate', $response['type']);
        self::assertArrayHasKey('duration', $response);
        self::assertArrayHasKey('enqueuedAt', $response);
        self::assertArrayHasKey('startedAt', $response);
        self::assertArrayHasKey('finishedAt', $response);
    }

    public function testExceptionWhenTaskTimeOut(): void
    {
        $res = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->expectException(TimeOutException::class);
        $this->index->waitForTask($res['taskUid'], 0, 20);
    }

    public function testDeleteIndexes(): void
    {
        $indexName1 = $this->safeIndexName('books-1');
        $this->index = $this->createEmptyIndex($indexName1);
        $indexName2 = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex($indexName2);

        $res = $this->index->delete();
        self::assertSame($indexName1, $res['indexUid']);
        self::assertArrayHasKey('type', $res);
        self::assertSame('indexDeletion', $res['type']);
        self::assertArrayHasKey('enqueuedAt', $res);

        $res = $index->delete();
        self::assertSame($indexName2, $res['indexUid']);
        self::assertArrayHasKey('type', $res);
        self::assertSame('indexDeletion', $res['type']);
        self::assertArrayHasKey('enqueuedAt', $res);
    }

    public function testSwapIndexes(): void
    {
        $promise = $this->client->swapIndexes([['indexA', 'indexB'], ['indexC', 'indexD']]);
        $response = $this->client->waitForTask($promise['taskUid']);

        self::assertSame([['indexes' => ['indexA', 'indexB']], ['indexes' => ['indexC', 'indexD']]], $response['details']['swaps']);
    }

    public function testDeleteTasks(): void
    {
        $promise = $this->client->deleteTasks((new DeleteTasksQuery())->setUids([1, 2]));
        $response = $this->client->waitForTask($promise['taskUid']);

        self::assertSame('?uids=1%2C2', $response['details']['originalFilter']);
        self::assertIsNumeric($response['details']['matchedTasks']);
    }

    public function testParseDate(): void
    {
        $date = '2021-01-01T01:23:45.123456Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        self::assertInstanceOf(\DateTimeInterface::class, $dateTime);
        self::assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWithExtraFractionalSeconds(): void
    {
        $date = '2021-01-01T01:23:45.123456789Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        self::assertInstanceOf(\DateTimeInterface::class, $dateTime);
        self::assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWhenNull(): void
    {
        $dateTime = Indexes::parseDate(null);

        self::assertNull($dateTime);
    }
}
