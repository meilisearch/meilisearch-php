<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

/**
 * @final since 1.3.0
 */
class CancelTasksQuery
{
    use TasksQueryTrait;
}
