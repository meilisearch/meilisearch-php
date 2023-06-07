<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Exceptions\TimeOutException;

/**
 * @final since 1.3.0
 */
class Tasks extends Endpoint
{
    protected const PATH = '/tasks';

    public function get($taskUid): array
    {
        return $this->http->get(self::PATH.'/'.$taskUid);
    }

    public function all(array $query = []): array
    {
        return $this->http->get(self::PATH.'/', $query);
    }

    public function cancelTasks(?CancelTasksQuery $options): array
    {
        $options = $options ?? new CancelTasksQuery();
        $response = $this->http->post('/tasks/cancel', null, $options->toArray());

        return $response;
    }

    public function deleteTasks(?DeleteTasksQuery $options): array
    {
        $options = $options ?? new DeleteTasksQuery();
        $response = $this->http->delete(self::PATH, $options->toArray());

        return $response;
    }

    /**
     * @throws TimeOutException
     */
    public function waitTask($taskUid, int $timeoutInMs, int $intervalInMs): array
    {
        $timeout_temp = 0;
        while ($timeoutInMs > $timeout_temp) {
            $res = $this->get($taskUid);
            if ('enqueued' != $res['status'] && 'processing' != $res['status']) {
                return $res;
            }
            $timeout_temp += $intervalInMs;
            usleep(1000 * $intervalInMs);
        }
        throw new TimeOutException();
    }

    public function waitTasks(array $taskUids, int $timeoutInMs, int $intervalInMs): array
    {
        $tasks = [];
        foreach ($taskUids as $taskUid) {
            $tasks[] = $this->waitTask($taskUid, $timeoutInMs, $intervalInMs);
        }

        return $tasks;
    }
}
