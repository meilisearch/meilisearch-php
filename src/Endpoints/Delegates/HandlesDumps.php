<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use MeiliSearch\Contracts\Task;
use Meilisearch\Endpoints\Dumps;

trait HandlesDumps
{
    protected Dumps $dumps;

    public function createDump(): Task
    {
        return $this->dumps->create();
    }
}
