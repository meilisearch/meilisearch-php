<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Tasks;

trait HandlesTasks
{
    protected Tasks $tasks;

    public function getTask(int $uid): Task
    {
        return $this->tasks->get($uid);
    }

    public function getTasks(?TasksQuery $options = null): TasksResults
    {
        $query = isset($options) ? $options->toArray() : [];

        $response = $this->tasks->all($query);

        return new TasksResults($response);
    }

    public function deleteTasks(?DeleteTasksQuery $options = null): Task
    {
        return $this->tasks->deleteTasks($options);
    }

    public function cancelTasks(?CancelTasksQuery $options = null): Task
    {
        return $this->tasks->cancelTasks($options);
    }
}
