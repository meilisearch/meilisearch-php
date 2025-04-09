<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Task;

class Snapshots extends Endpoint
{
    protected const PATH = '/snapshots';

    public function create(): Task
    {
        return Task::fromArray($this->http->post(self::PATH));
    }
}
