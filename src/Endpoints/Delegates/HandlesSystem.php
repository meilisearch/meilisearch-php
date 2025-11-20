<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Stats as StatsContract;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Health;
use Meilisearch\Endpoints\Stats;
use Meilisearch\Endpoints\TenantToken;
use Meilisearch\Endpoints\Version;
use Meilisearch\Exceptions\LogicException;

trait HandlesSystem
{
    protected Health $health;
    protected Version $version;
    protected TenantToken $tenantToken;
    protected Stats $stats;

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

    public function stats(): StatsContract
    {
        $stats = $this->stats->show();

        if (!\is_array($stats)) {
            throw new LogicException('Stats did not respond with valid data.');
        }

        return StatsContract::fromArray($stats);
    }

    public function generateTenantToken(string $apiKeyUid, $searchRules, array $options = []): string
    {
        return $this->tenantToken->generateTenantToken($apiKeyUid, $searchRules, $options);
    }

    public function swapIndexes(array $indexes): Task
    {
        $options = array_map(static fn ($data) => ['indexes' => $data], $indexes);

        return $this->index->swapIndexes($options);
    }
}
