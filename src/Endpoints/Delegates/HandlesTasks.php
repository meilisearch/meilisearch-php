<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Contracts\CancelTasksQuery;
use MeiliSearch\Contracts\DeleteTasksQuery;
use MeiliSearch\Contracts\TasksQuery;
use MeiliSearch\Contracts\TasksResults;

trait HandlesTasks
{
    public function getTask($uid): array
    {
        return $this->tasks->get($uid);
    }

    public function getTasks(TasksQuery $options = null): TasksResults
    {
        $query = isset($options) ? $options->toArray() : [];

        $response = $this->tasks->all($query);

        return new TasksResults($response);
    }

    public function deleteTasks(DeleteTasksQuery $options = null): array
    {
        return $this->tasks->deleteTasks($options);
    }

    public function cancelTasks(CancelTasksQuery $options = null): array
    {
        return $this->tasks->cancelTasks($options);
    }

    public function waitForTask($uid, int $timeoutInMs = 5000, int $intervalInMs = 50): array
    {
        return $this->tasks->waitTask($uid, $timeoutInMs, $intervalInMs);
    }

    public function waitForTasks($uids, int $timeoutInMs = 5000, int $intervalInMs = 50): array
    {
        return $this->tasks->waitTasks($uids, $timeoutInMs, $intervalInMs);
    }
}
