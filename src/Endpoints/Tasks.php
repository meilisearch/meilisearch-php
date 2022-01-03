<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Exceptions\TimeOutException;

class Tasks extends Endpoint
{
    protected const PATH = '/tasks';

    public function get($uid): array
    {
        return $this->http->get(self::PATH.'/'.$uid);
    }

    public function all(): array
    {
        return $this->http->get(self::PATH.'/');
    }

    public function getIndexTask($path, $uid): array
    {
        return $this->http->get($path.self::PATH.'/'.$uid);
    }

    public function allIndexTask($path): array
    {
        return $this->http->get($path.self::PATH.'/');
    }

    /**
     * @param string $uid
     * @param int    $timeoutInMs
     * @param int    $intervalInMs
     *
     * @throws TimeOutException
     */
    public function waitTask($uid, $timeoutInMs, $intervalInMs): array
    {
        $timeout_temp = 0;
        while ($timeoutInMs > $timeout_temp) {
            $res = $this->get($uid);
            if ('enqueued' != $res['status'] && 'processing' != $res['status']) {
                return $res;
            }
            $timeout_temp += $intervalInMs;
            usleep(1000 * $intervalInMs);
        }
        throw new TimeOutException();
    }

    /**
     * @param array $uids
     * @param int   $timeoutInMs
     * @param int   $intervalInMs
     *
     * @throws TimeOutException
     */
    public function waitAllTasks($uids, $timeoutInMs, $intervalInMs): array
    {
        $tasks = [];
        foreach ($uids as $uid) {
            $tasks[] = $this->waitTask($uid, $timeoutInMs, $intervalInMs);
        }

        return $tasks;
    }
}
