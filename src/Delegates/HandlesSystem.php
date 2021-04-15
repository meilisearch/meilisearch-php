<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Health;
use MeiliSearch\Endpoints\Keys;
use MeiliSearch\Endpoints\Stats;
use MeiliSearch\Endpoints\SysInfo;
use MeiliSearch\Endpoints\Version;

/**
 * @property Health health
 * @property Version version
 * @property SysInfo sysInfo
 * @property Stats stats
 * @property Keys keys
 */
trait HandlesSystem
{
    public function health(): ?array
    {
        return $this->health->show();
    }

    public function isHealthy(): bool
    {
        try {
            $this->health->show();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function version(): array
    {
        return $this->version->show();
    }

    public function stats(): array
    {
        return $this->stats->show();
    }

    public function getKeys(): array
    {
        return $this->keys->show();
    }
}
