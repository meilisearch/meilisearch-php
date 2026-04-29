<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\TaskCancelationDetails;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
use Meilisearch\Endpoints\Index;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class TasksTest extends TestCase
{
    private Index $index;
    private string $indexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexName = $this->safeIndexName();
        $this->index = $this->createEmptyIndex($this->indexName);
    }

    public function testGetOneTaskFromWaitTask(): void
    {
        [$task, $completedTask] = $this->seedIndex();

        self::assertSame($completedTask->getTaskUid(), $task->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $completedTask->getType());
        self::assertSame($this->indexName, $completedTask->getIndexUid());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $completedTask->getDetails());
    }

    public function testGetOneTaskClient(): void
    {
        [$seedTask] = $this->seedIndex();

        $task = $this->index->getTask($seedTask->getTaskUid());
        self::assertSame($task->getTaskUid(), $seedTask->getTaskUid());
        self::assertSame(TaskType::DocumentAdditionOrUpdate, $task->getType());
        self::assertSame($this->indexName, $task->getIndexUid());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $task->getDetails());
    }

    public function testGetTaskDocumentsClient(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['getTaskDocumentsRoute' => true]);

        // The task documents route only exposes documents while the task is
        // still enqueued or processing. Enqueue a large batch first so the task
        // we inspect stays queued behind it instead of completing immediately.
        $this->index->updateDocuments(array_map(
            static fn (int $id): array => ['id' => $id],
            range(1_000_000, 1_010_000)
        ));
        $task = $this->index->updateDocuments(self::DOCUMENTS);

        $stream = (string) $this->client->getTaskDocuments($task->getTaskUid());

        // Meilisearch streams the documents as consecutive JSON objects. Despite
        // the application/x-ndjson content type they are not newline-delimited,
        // so decode them as a sequence of concatenated JSON values.
        $documents = self::decodeJsonObjectStream($stream);

        self::assertCount(\count(self::DOCUMENTS), $documents);
        self::assertArrayHasKey('id', $documents[0], 'Each document should have an id field');
    }

    /**
     * Decodes a stream of concatenated JSON objects (e.g. {"id":1}{"id":2}) into
     * a list of associative arrays, tracking string/escape state so object
     * boundaries inside string values are ignored.
     *
     * @return list<array<string, mixed>>
     */
    private static function decodeJsonObjectStream(string $payload): array
    {
        $documents = [];
        $depth = 0;
        $start = null;
        $inString = false;
        $escaped = false;
        $length = \strlen($payload);

        for ($i = 0; $i < $length; ++$i) {
            $char = $payload[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ('\\' === $char) {
                    $escaped = true;
                } elseif ('"' === $char) {
                    $inString = false;
                }

                continue;
            }

            if ('"' === $char) {
                $inString = true;
            } elseif ('{' === $char) {
                if (0 === $depth) {
                    $start = $i;
                }
                ++$depth;
            } elseif ('}' === $char) {
                --$depth;
                if (0 === $depth && null !== $start) {
                    $documents[] = json_decode(substr($payload, $start, $i - $start + 1), true, 512, \JSON_THROW_ON_ERROR);
                    $start = null;
                }
            }
        }

        return $documents;
    }

    public function testGetAllTasksClient(): void
    {
        $tasks = $this->client->getTasks();
        $firstIndex = $tasks->getResults()[0]->getTaskUid();
        $this->seedIndex();

        $tasks = $this->client->getTasks();
        $newFirstIndex = $tasks->getResults()[0]->getTaskUid();

        self::assertNotSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksClientWithPagination(): void
    {
        $tasks = $this->client->getTasks((new TasksQuery())->setLimit(0));

        self::assertSame([], $tasks->getResults());
    }

    public function getAllTasksClientWithBatchFilter(): void
    {
        [$task] = $this->seedIndex();
        $task = $this->client->getTask($task->getTaskUid());

        $tasks = $this->client->getTasks(
            (new TasksQuery())
                ->setBatchUid($task->getTaskUid())
        );

        self::assertGreaterThan(0, $tasks->getTotal());
    }

    public function testGetAllTasksByIndex(): void
    {
        $response = $this->index->getTasks();
        $firstIndex = $response->getResults()[0]->getTaskUid();

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]->getTaskUid();

        self::assertSame($firstIndex, $newFirstIndex);
    }

    public function testGetAllTasksByIndexWithFilter(): void
    {
        $response = $this->index->getTasks((new TasksQuery())
            ->setAfterEnqueuedAt(new \DateTime('yesterday'))->setStatuses(['succeeded'])->setLimit(2));

        $firstIndex = $response->getResults()[0]->getTaskUid();
        self::assertSame(TaskStatus::Succeeded, $response->getResults()[0]->getStatus());

        $newIndex = $this->createEmptyIndex($this->safeIndexName('movie-1'));
        $newIndex->updateDocuments(self::DOCUMENTS);

        $response = $this->index->getTasks();
        $newFirstIndex = $response->getResults()[0]->getTaskUid();

        self::assertSame($firstIndex, $newFirstIndex);
        self::assertGreaterThan(0, $response->getTotal());
    }

    public function testCancelTasksWithFilter(): void
    {
        $date = new \DateTime('yesterday');
        $query = http_build_query(['afterEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
        $task = $this->client->cancelTasks((new CancelTasksQuery())->setAfterEnqueuedAt($date))->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());
        self::assertInstanceOf(TaskCancelationDetails::class, $details = $task->getDetails());
        self::assertSame('?'.$query, $details->originalFilter);
    }

    public function testGetAllTasksInReverseOrder(): void
    {
        $sampleTasks = $this->client->getTasks(new TasksQuery());

        $sampleTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $sampleTasks->getResults());

        $expectedTasks = $this->client->getTasks((new TasksQuery())->setUids($sampleTasksUids));
        $expectedTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $expectedTasks->getResults());

        $reversedTasks = $this->client->getTasks((new TasksQuery())->setUids($sampleTasksUids)->setReverse(true));
        $reversedTasksUids = array_map(static fn (Task $task) => $task->getTaskUid(), $reversedTasks->getResults());

        self::assertSame(array_reverse($expectedTasksUids), $reversedTasksUids);
    }

    public function testExceptionIfNoTaskIdWhenGetting(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Task `99999999` not found.');

        $this->index->getTask(99999999);
    }

    /**
     * @return array{0: Task, 1: Task}
     */
    private function seedIndex(): array
    {
        $task = $this->index->updateDocuments(self::DOCUMENTS);

        return [$task, $task->wait()];
    }
}
