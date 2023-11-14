<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Endpoints\Snapshots;

trait HandlesSnapshots
{
    protected Snapshots $snapshots;

    public function createSnapshot(): array
    {
        return $this->snapshots->create();
    }
}
