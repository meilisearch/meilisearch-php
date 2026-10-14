<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Psr\Http\Message\StreamInterface;

use function Meilisearch\partial;
use function Meilisearch\waitForTask;

/**
 * @phpstan-import-type RawTask from Task
 *
 * @phpstan-type RawTasks array{
 *     results: array<int, RawTask>,
 *     from: non-negative-int|null,
 *     limit: non-negative-int,
 *     next: non-negative-int|null,
 *     total: non-negative-int
 * }
 */
class Tasks extends Endpoint
{
    protected const PATH = '/tasks';

    public function get(int $taskUid): Task
    {
        return Task::fromArray($this->http->get(self::PATH.'/'.$taskUid), partial(waitForTask(...), $this->http));
    }

    public function getDocuments(int $taskUid): StreamInterface
    {
        return $this->http->getStream(self::PATH.'/'.$taskUid.'/documents');
    }

    public function all(?TasksQuery $query = null): TasksResults
    {
        $rawData = $this->http->get(self::PATH.'/', $query?->toArray() ?? []);
        /** @var RawTasks $rawTasks */
        $rawTasks = $rawData;
        $results = array_map(fn (array $task): Task => Task::fromArray($task, partial(waitForTask(...), $this->http)), $rawTasks['results']);

        return new TasksResults([
            'results' => $results,
            'from' => $rawTasks['from'],
            'limit' => $rawTasks['limit'],
            'next' => $rawTasks['next'],
            'total' => $rawTasks['total'],
        ]);
    }

    public function cancelTasks(?CancelTasksQuery $options): Task
    {
        $options = $options ?? new CancelTasksQuery();

        return Task::fromArray($this->http->post('/tasks/cancel', null, $options->toArray()), partial(waitForTask(...), $this->http));
    }

    public function deleteTasks(?DeleteTasksQuery $options): Task
    {
        $options = $options ?? new DeleteTasksQuery();

        return Task::fromArray($this->http->delete(self::PATH, $options->toArray()), partial(waitForTask(...), $this->http));
    }
}
