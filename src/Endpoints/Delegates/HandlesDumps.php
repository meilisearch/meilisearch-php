<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Endpoints\Dumps;

trait HandlesDumps
{
    protected Dumps $dumps;

    public function createDump(): array
    {
        return $this->dumps->create();
    }
}
