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
        $this->assertSame([], $this->index->getSynonyms());
        $this->assertSame([], $this->index->getStopWords());
        $this->assertSame([], $this->index->getSortableAttributes());
        $this->assertSame(['*'], $this->index->getSearchableAttributes());
        $this->assertSame(
            ['words', 'typo', 'proximity', 'attribute', 'sort', 'exactness'],
            $this->index->getRankingRules()
        );
        $this->assertSame([], $this->index->getFilterableAttributes());
        $this->assertSame(['*'], $this->index->getDisplayedAttributes());
        $this->assertSame(['maxValuesPerFacet' => 100], $this->index->getFaceting());
        $this->assertSame(['maxTotalHits' => 1000], $this->index->getPagination());
        $this->assertSame(
            [
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 5, 'twoTypos' => 9],
                'disableOnWords' => [],
                'disableOnAttributes' => [],
            ],
            $this->index->getTypoTolerance(),
        );
    }

    public function testGetPrimaryKey(): void
    {
        $index = $this->createEmptyIndex(
            $this->safeIndexName('books-2'),
            ['primaryKey' => 'objectId']
        );

        $this->assertNull($this->index->getPrimaryKey());
        $this->assertSame('objectId', $index->getPrimaryKey());
    }

    public function testGetUid(): void
    {
        $indexName = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectId']
        );
        $this->assertSame($this->indexName, $this->index->getUid());
        $this->assertSame($indexName, $index->getUid());
    }

    public function testGetCreatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        $this->assertNull($indexB->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->index->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $indexB = $this->client->index('indexB');

        $this->assertNull($indexB->getUpdatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->index->getUpdatedAt());
    }

    public function testFetchRawInfo(): void
    {
        $indexName = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectId']
        );

        $response = $index->fetchRawInfo();

        $this->assertArrayHasKey('primaryKey', $response);
        $this->assertArrayHasKey('uid', $response);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);
        $this->assertSame($response['primaryKey'], 'objectId');
        $this->assertSame($response['uid'], $indexName);
    }

    public function testPrimaryKeyUpdate(): void
    {
        $primaryKey = 'id';

        $response = $this->index->update(['primaryKey' => $primaryKey]);
        $this->client->waitForTask($response['taskUid']);
        $index = $this->client->getIndex($response['indexUid']);

        $this->assertSame($index->getPrimaryKey(), $primaryKey);
        $this->assertSame($index->getUid(), $this->indexName);
        $this->assertSame($index->getPrimaryKey(), $primaryKey);
        $this->assertSame($this->index->getUid(), $this->indexName);
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
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($indexName);
        $this->assertNull($index->getPrimaryKey());

        $index = $index->fetchInfo();
        $this->assertSame('objectID', $index->getPrimaryKey());
        $this->assertSame($indexName, $index->getUid());
        $this->assertInstanceOf(\DateTimeInterface::class, $index->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $index->getUpdatedAt());
    }

    public function testGetAndFetchPrimaryKey(): void
    {
        $indexName = $this->safeIndexName('books-1');
        $this->createEmptyIndex(
            $indexName,
            ['primaryKey' => 'objectID']
        );

        $index = $this->client->index($indexName);
        $this->assertNull($index->getPrimaryKey());
        $this->assertSame('objectID', $index->fetchPrimaryKey());
        $this->assertSame('objectID', $index->getPrimaryKey());
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

        $this->assertSame($expected, $results);
    }

    public function testWaitForTaskDefault(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $response = $this->index->waitForTask($promise['taskUid']);

        /* @phpstan-ignore-next-line */
        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeoutAndInterval(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['taskUid'], 100, 20);

        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
    }

    public function testWaitForTaskWithTimeout(): void
    {
        $promise = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $response = $this->index->waitForTask($promise['taskUid'], 100);

        $this->assertSame($response['status'], 'succeeded');
        $this->assertSame($response['uid'], $promise['taskUid']);
        $this->assertArrayHasKey('type', $response);
        $this->assertSame($response['type'], 'documentAdditionOrUpdate');
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('startedAt', $response);
        $this->assertArrayHasKey('finishedAt', $response);
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
        $this->assertSame($res['indexUid'], $indexName1);
        $this->assertArrayHasKey('type', $res);
        $this->assertSame($res['type'], 'indexDeletion');
        $this->assertArrayHasKey('enqueuedAt', $res);

        $res = $index->delete();
        $this->assertSame($res['indexUid'], $indexName2);
        $this->assertArrayHasKey('type', $res);
        $this->assertSame($res['type'], 'indexDeletion');
        $this->assertArrayHasKey('enqueuedAt', $res);
    }

    public function testSwapIndexes(): void
    {
        $promise = $this->client->swapIndexes([['indexA', 'indexB'], ['indexC', 'indexD']]);
        $response = $this->client->waitForTask($promise['taskUid']);

        $this->assertSame($response['details']['swaps'], [['indexes' => ['indexA', 'indexB']], ['indexes' => ['indexC', 'indexD']]]);
    }

    public function testDeleteTasks(): void
    {
        $promise = $this->client->deleteTasks((new DeleteTasksQuery())->setUids([1, 2]));
        $response = $this->client->waitForTask($promise['taskUid']);

        $this->assertSame($response['details']['originalFilter'], '?uids=1%2C2');
        $this->assertIsNumeric($response['details']['matchedTasks']);
    }

    public function testParseDate(): void
    {
        $date = '2021-01-01T01:23:45.123456Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        $this->assertInstanceOf(\DateTimeInterface::class, $dateTime);
        $this->assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWithExtraFractionalSeconds(): void
    {
        $date = '2021-01-01T01:23:45.123456789Z';
        $dateTime = Indexes::parseDate($date);
        $formattedDate = '2021-01-01T01:23:45.123456+00:00';

        $this->assertInstanceOf(\DateTimeInterface::class, $dateTime);
        $this->assertSame($formattedDate, $dateTime->format('Y-m-d\TH:i:s.uP'));
    }

    public function testParseDateWhenNull(): void
    {
        $dateTime = Indexes::parseDate(null);

        $this->assertNull($dateTime);
    }
}
