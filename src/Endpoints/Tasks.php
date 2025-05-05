<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Task;
use Meilisearch\Exceptions\TimeOutException;

class Tasks extends Endpoint
{
    protected const PATH = '/tasks';

    public function get(int $taskUid): Task
    {
        return Task::fromArray($this->http->get(self::PATH.'/'.$taskUid));
    }

    public function all(array $query = []): array
    {
        return $this->http->get(self::PATH.'/', $query);
    }

    public function cancelTasks(?CancelTasksQuery $options): Task
    {
        $options = $options ?? new CancelTasksQuery();

        return Task::fromArray($this->http->post('/tasks/cancel', null, $options->toArray()));
    }

    public function deleteTasks(?DeleteTasksQuery $options): Task
    {
        $options = $options ?? new DeleteTasksQuery();

        return Task::fromArray($this->http->delete(self::PATH, $options->toArray()));
    }

    /**
     * @throws TimeOutException
     */
    public function waitTask(int $taskUid, int $timeoutInMs, int $intervalInMs): Task
    {
        $timeoutTemp = 0;

        while ($timeoutInMs > $timeoutTemp) {
            $task = $this->get($taskUid);

            if ($task->isFinished()) {
                return $task;
            }

            $timeoutTemp += $intervalInMs;
            usleep(1000 * $intervalInMs);
        }

        throw new TimeOutException();
    }

    /**
     * @param array<int> $taskUids
     *
     * @throws TimeOutException
     */
    public function waitTasks(array $taskUids, int $timeoutInMs, int $intervalInMs): array
    {
        $tasks = [];

        foreach ($taskUids as $taskUid) {
            $tasks[] = $this->waitTask($taskUid, $timeoutInMs, $intervalInMs);
        }

        return $tasks;
    }
}
