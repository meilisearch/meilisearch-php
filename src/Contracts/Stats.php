<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class Stats
{
    /**
     * @param int|string                          $databaseSize     Bytes when sizeFormat is 'raw', human-readable string when 'human'
     * @param int|string                          $usedDatabaseSize Bytes when sizeFormat is 'raw', human-readable string when 'human'
     * @param array<non-empty-string, IndexStats> $indexes
     */
    public function __construct(
        private readonly int|string $databaseSize,
        private readonly int|string $usedDatabaseSize,
        private readonly ?\DateTimeImmutable $lastUpdate,
        private readonly array $indexes,
    ) {
    }

    /**
     * Returns the total database size.
     * Value is an integer (bytes) when `sizeFormat` is `'raw'` (default),
     * or a human-readable string such as `"2.3 MiB"` when `sizeFormat` is `'human'`.
     *
     * @return int|string
     */
    public function getDatabaseSize(): int|string
    {
        return $this->databaseSize;
    }

    /**
     * Returns the used database size.
     * Value is an integer (bytes) when `sizeFormat` is `'raw'` (default),
     * or a human-readable string such as `"2.3 MiB"` when `sizeFormat` is `'human'`.
     *
     * @return int|string
     */
    public function getUsedDatabaseSize(): int|string
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
     *     databaseSize: int|string,
     *     usedDatabaseSize: int|string,
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
