<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Endpoints\Dumps;

/**
 * @property Dumps dumps
 */
trait HandlesDumps
{
    public function createDump(): array
    {
        return $this->dumps->create();
    }

    public function getDumpStatus(string $uid): array
    {
        return $this->dumps->status($uid);
    }
}
