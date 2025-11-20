<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class Stats
{
    /**
     * @param non-negative-int                    $databaseSize
     * @param non-negative-int                    $usedDatabaseSize
     * @param array<non-empty-string, IndexStats> $indexes
     */
    public function __construct(
        private readonly int $databaseSize,
        private readonly int $usedDatabaseSize,
        private readonly ?\DateTimeImmutable $lastUpdate,
        private readonly array $indexes,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getDatabaseSize(): int
    {
        return $this->databaseSize;
    }

    /**
     * @return non-negative-int
     */
    public function getUsedDatabaseSize(): int
    {
        return $this->usedDatabaseSize;
    }

    public function getLastUpdate(): ?\DateTimeImmutable
    {
        return $this->lastUpdate;
    }

    /**
     * @return array<non-empty-string, IndexStats>
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param array{
     *     databaseSize: non-negative-int,
     *     usedDatabaseSize: non-negative-int,
     *     lastUpdate: non-empty-string|null,
     *     indexes: array<non-empty-string, mixed>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['databaseSize'],
            $data['usedDatabaseSize'],
            null !== $data['lastUpdate'] ? new \DateTimeImmutable($data['lastUpdate']) : null,
            array_map(static fn (array $v) => IndexStats::fromArray($v), $data['indexes']),
        );
    }
}
