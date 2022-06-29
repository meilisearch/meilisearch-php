<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

trait HandlesDumps
{
    public function createDump(): array
    {
        return $this->dumps->create();
    }
}
