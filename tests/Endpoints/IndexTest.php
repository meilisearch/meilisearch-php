<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskDetails\IndexSwapDetails;
use Meilisearch\Contracts\TaskDetails\TaskDeletionDetails;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
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
                'disableOnNumbers' => false,
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

        $task = $this->index->update(['primaryKey' => $primaryKey]);
        $this->client->waitForTask($task->getTaskUid());

        $index = $this->client->getIndex($task->getIndexUid());

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
        $task = $this->client->createIndex('new-index', ['primaryKey' => 'objectID']);
        $this->index->waitForTask($task->getTaskUid());
        $task = $this->client->createIndex('other-index', ['primaryKey' => 'objectID']);
        $this->index->waitForTask($task->getTaskUid());
        $task = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $this->index->waitForTask($task->getTaskUid());

        $tasks = $this->index->getTasks((new TasksQuery())->setIndexUids(['other-index']));

        $allIndexUids = array_map(static fn (Task $t) => $t->getIndexUid(), $tasks->getResults());
        $results = array_unique($allIndexUids);
        $expected = [$this->index->getUid(), 'other-index'];

        self::assertSame($expected, $results);
    }

    public function testWaitForTaskDefault(): void
    {
        $task = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $completedTask = $this->index->waitForTask($task->getTaskUid());

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame($completedTask->getTaskUid(), $task->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $task->getType());
        self::assertNotNull($completedTask->getDuration());
        self::assertNotNull($completedTask->getStartedAt());
        self::assertNotNull($completedTask->getFinishedAt());
    }

    public function testWaitForTaskWithTimeoutAndInterval(): void
    {
        $task = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $completedTask = $this->index->waitForTask($task->getTaskUid(), 100, 20);

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame($completedTask->getTaskUid(), $task->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $task->getType());
        self::assertNotNull($completedTask->getDuration());
        self::assertNotNull($completedTask->getStartedAt());
        self::assertNotNull($completedTask->getFinishedAt());
    }

    public function testWaitForTaskWithTimeout(): void
    {
        $task = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);
        $completedTask = $this->index->waitForTask($task->getTaskUid(), 1000);

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame($completedTask->getTaskUid(), $task->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $task->getType());
        self::assertNotNull($completedTask->getDuration());
        self::assertNotNull($completedTask->getStartedAt());
        self::assertNotNull($completedTask->getFinishedAt());
    }

    public function testExceptionWhenTaskTimeOut(): void
    {
        $task = $this->index->addDocuments([['id' => 1, 'title' => 'Pride and Prejudice']]);

        $this->expectException(TimeOutException::class);

        $this->index->waitForTask($task->getTaskUid(), 0, 20);
    }

    public function testDeleteIndexes(): void
    {
        $indexName1 = $this->safeIndexName('books-1');
        $this->index = $this->createEmptyIndex($indexName1);
        $indexName2 = $this->safeIndexName('books-2');
        $index = $this->createEmptyIndex($indexName2);

        $task = $this->index->delete();
        self::assertSame($indexName1, $task->getIndexUid());
        self::assertSame(TaskType::IndexDeletion, $task->getType());

        $task = $index->delete();
        self::assertSame($indexName2, $task->getIndexUid());
        self::assertSame(TaskType::IndexDeletion, $task->getType());
    }

    public function testSwapIndexes(): void
    {
        $task = $this->client->swapIndexes([['indexA', 'indexB'], ['indexC', 'indexD']]);
        $completedTask = $this->client->waitForTask($task->getTaskUid());

        self::assertSame(['indexA', 'indexB'], $response['details']['swaps'][0]['indexes']);
        self::assertSame(['indexC', 'indexD'], $response['details']['swaps'][1]['indexes']);
        self::assertFalse($response['details']['swaps'][0]['rename']);
        self::assertFalse($response['details']['swaps'][1]['rename']);
//        self::assertInstanceOf(IndexSwapDetails::class, $details = $completedTask->getDetails());
//        self::assertSame([['indexes' => ['indexA', 'indexB']], ['indexes' => ['indexC', 'indexD']]], $details->swaps);
    }

    public function testDeleteTasks(): void
    {
        $task = $this->client->deleteTasks((new DeleteTasksQuery())->setUids([1, 2]));
        $completedTask = $this->client->waitForTask($task->getTaskUid());

        self::assertInstanceOf(TaskDeletionDetails::class, $details = $completedTask->getDetails());
        self::assertSame('?uids=1%2C2', $details->originalFilter);
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
