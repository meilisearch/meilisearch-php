<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use MeiliSearch\Contracts\Task;
use Meilisearch\Endpoints\Snapshots;

trait HandlesSnapshots
{
    protected Snapshots $snapshots;

    public function createSnapshot(): Task
    {
        return $this->snapshots->create();
    }
}
