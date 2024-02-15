<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Tasks;
use Meilisearch\Exceptions\TimeOutException;

trait HandlesTasks
{
    protected Tasks $tasks;

    public function getTask($uid): array
    {
        return $this->tasks->get($uid);
    }

    public function getTasks(?TasksQuery $options = null): TasksResults
    {
        $query = isset($options) ? $options->toArray() : [];

        $response = $this->tasks->all($query);

        return new TasksResults($response);
    }

    public function deleteTasks(?DeleteTasksQuery $options = null): array
    {
        return $this->tasks->deleteTasks($options);
    }

    public function cancelTasks(?CancelTasksQuery $options = null): array
    {
        return $this->tasks->cancelTasks($options);
    }

    /**
     * @throws TimeOutException
     */
    public function waitForTask($uid, int $timeoutInMs = 5000, int $intervalInMs = 50): array
    {
        return $this->tasks->waitTask($uid, $timeoutInMs, $intervalInMs);
    }

    /**
     * @throws TimeOutException
     */
    public function waitForTasks($uids, int $timeoutInMs = 5000, int $intervalInMs = 50): array
    {
        return $this->tasks->waitTasks($uids, $timeoutInMs, $intervalInMs);
    }
}
