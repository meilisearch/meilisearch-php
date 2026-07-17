<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Health as HealthContract;
use Meilisearch\Contracts\IndexStats;
use Meilisearch\Contracts\Stats as StatsContract;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\Version as VersionContract;
use Meilisearch\Endpoints\Health;
use Meilisearch\Endpoints\Stats;
use Meilisearch\Endpoints\TenantToken;
use Meilisearch\Endpoints\Version;
use Meilisearch\Exceptions\LogicException;

/**
 * @phpstan-import-type RawIndexStats from IndexStats
 */
trait HandlesSystem
{
    protected Health $health;
    protected Version $version;
    protected TenantToken $tenantToken;
    protected Stats $stats;

    public function health(): HealthContract
    {
        $health = $this->health->show();

        if (!\is_array($health)) {
            throw new LogicException('Health did not respond with valid data.');
        }

        /** @var array{status: non-empty-string} $rawHealth */
        $rawHealth = $health;

        return HealthContract::fromArray($rawHealth);
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

    public function version(): VersionContract
    {
        $version = $this->version->show();

        if (!\is_array($version)) {
            throw new LogicException('Version did not respond with valid data.');
        }

        /** @var array{commitSha: non-empty-string, commitDate: non-empty-string, pkgVersion: non-empty-string} $rawVersion */
        $rawVersion = $version;

        return VersionContract::fromArray($rawVersion);
    }

    public function stats(): StatsContract
    {
        $stats = $this->stats->show();

        if (!\is_array($stats)) {
            throw new LogicException('Stats did not respond with valid data.');
        }

        /** @var array{
         *     databaseSize: non-negative-int,
         *     usedDatabaseSize: non-negative-int,
         *     lastUpdate: non-empty-string|null,
         *     indexes: array<non-empty-string, RawIndexStats>
         * } $rawStats
         */
        $rawStats = $stats;

        return StatsContract::fromArray($rawStats);
    }

    /**
     * @param array{apiKey?: string|null, expiresAt?: \DateTimeInterface|null} $options
     */
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
