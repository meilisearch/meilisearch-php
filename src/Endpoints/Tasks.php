<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Exceptions\TimeOutException;

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
