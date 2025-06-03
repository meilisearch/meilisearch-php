<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Task;
use Meilisearch\Exceptions\TimeOutException;

use function Meilisearch\partial;

class Tasks extends Endpoint
{
    protected const PATH = '/tasks';

    public function get(int $taskUid): Task
    {
        return Task::fromArray($this->http->get(self::PATH.'/'.$taskUid), partial(self::waitTask(...), $this->http));
    }

    // @todo: must return array<Task>
    public function all(array $query = []): array
    {
        return $this->http->get(self::PATH.'/', $query);
    }

    public function cancelTasks(?CancelTasksQuery $options): Task
    {
        $options = $options ?? new CancelTasksQuery();

        return Task::fromArray($this->http->post('/tasks/cancel', null, $options->toArray()), partial(self::waitTask(...), $this->http));
    }

    public function deleteTasks(?DeleteTasksQuery $options): Task
    {
        $options = $options ?? new DeleteTasksQuery();

        return Task::fromArray($this->http->delete(self::PATH, $options->toArray()), partial(self::waitTask(...), $this->http));
    }

    /**
     * @internal
     *
     * @throws TimeOutException
     */
    public static function waitTask(Http $http, int $taskUid, int $timeoutInMs, int $intervalInMs): Task
    {
        $timeoutTemp = 0;

        while ($timeoutInMs > $timeoutTemp) {
            $task = Task::fromArray($http->get(self::PATH.'/'.$taskUid), partial(self::waitTask(...), $http));

            if ($task->isFinished()) {
                return $task;
            }

            $timeoutTemp += $intervalInMs;
            usleep(1000 * $intervalInMs);
        }

        throw new TimeOutException();
    }
}
