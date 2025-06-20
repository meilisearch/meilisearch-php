<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;

class Snapshots extends Endpoint
{
    protected const PATH = '/snapshots';

    public function create(): Task
    {
        return Task::fromArray($this->http->post(self::PATH), partial(Tasks::waitTask(...), $this->http));
    }
}
