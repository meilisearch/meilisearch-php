<?php

declare(strict_types=1);

namespace Meilisearch;

/**
 * @internal
 *
 * Creates a partially applied function by binding initial arguments to the given callable.
 *
 * Returns a Closure that, when invoked, calls the original callable with the bound arguments prepended
 * to any new ones.
 *
 * Used internally to build reusable “waiter” functions (e.g., binding the HTTP client to
 * task-waiting logic) and reduce repetitive argument passing.
 */
function partial(callable $func, ...$boundArgs): \Closure
{
    return static function (...$remainingArgs) use ($func, $boundArgs) {
        return $func(...array_merge($boundArgs, $remainingArgs));
    };
}

/**
 * @internal
 *
 * @throws Exceptions\TimeoutException
 */
function waitForTask(Contracts\Http $http, int $taskUid, int $timeoutInMs, int $intervalInMs): Contracts\Task
{
    $timeoutTemp = 0;
    $deadlineNs = hrtime(true) + ($timeoutInMs * 1_000_000);

    while ($timeoutInMs > $timeoutTemp) {
        $task = Contracts\Task::fromArray($http->get('/tasks/'.$taskUid), partial(waitForTask(...), $http));

        if ($task->isFinished()) {
            return $task;
        }

        if ($intervalInMs > 0) {
            $timeoutTemp += $intervalInMs;
            usleep(1000 * $intervalInMs);

            continue;
        }

        if (hrtime(true) >= $deadlineNs) {
            break;
        }
    }

    throw new Exceptions\TimeoutException();
}

/**
 * @internal
 *
 * RFC 3339 timestamp in UTC with microsecond precision
 */
function formatRfc3339Micro(\DateTimeInterface $date): string
{
    return \DateTimeImmutable::createFromInterface($date)
        ->setTimezone(new \DateTimeZone('UTC'))
        ->format('Y-m-d\TH:i:s.u\Z');
}
