<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-import-type RawBatchEmbedderRequests from BatchEmbedderRequests
 *
 * @phpstan-type RawBatchStats array{
 *     totalNbTasks: non-negative-int,
 *     status: array<non-empty-string, non-negative-int>,
 *     types: array<non-empty-string, non-negative-int>,
 *     indexUids: array<non-empty-string, non-negative-int>,
 *     progressTrace?: array<string, mixed>,
 *     writeChannelCongestion?: array<string, mixed>,
 *     internalDatabaseSizes?: array<string, mixed>,
 *     embedderRequests?: RawBatchEmbedderRequests
 * }
 */
final class BatchStats
{
    /**
     * @param non-negative-int                          $totalNbTasks
     * @param array<non-empty-string, non-negative-int> $status
     * @param array<non-empty-string, non-negative-int> $types
     * @param array<non-empty-string, non-negative-int> $indexUids
     * @param array<string, mixed>|null                 $progressTrace
     * @param array<string, mixed>|null                 $writeChannelCongestion
     * @param array<string, mixed>|null                 $internalDatabaseSizes
     */
    public function __construct(
        private readonly int $totalNbTasks,
        private readonly array $status,
        private readonly array $types,
        private readonly array $indexUids,
        private readonly ?array $progressTrace = null,
        private readonly ?array $writeChannelCongestion = null,
        private readonly ?array $internalDatabaseSizes = null,
        private readonly ?BatchEmbedderRequests $embedderRequests = null,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getTotalNbTasks(): int
    {
        return $this->totalNbTasks;
    }

    /**
     * @return array<non-empty-string, non-negative-int>
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * @return array<non-empty-string, non-negative-int>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array<non-empty-string, non-negative-int>
     */
    public function getIndexUids(): array
    {
        return $this->indexUids;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProgressTrace(): ?array
    {
        return $this->progressTrace;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWriteChannelCongestion(): ?array
    {
        return $this->writeChannelCongestion;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getInternalDatabaseSizes(): ?array
    {
        return $this->internalDatabaseSizes;
    }

    public function getEmbedderRequests(): ?BatchEmbedderRequests
    {
        return $this->embedderRequests;
    }

    /**
     * @param RawBatchStats $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['totalNbTasks'],
            $data['status'],
            $data['types'],
            $data['indexUids'],
            $data['progressTrace'] ?? null,
            $data['writeChannelCongestion'] ?? null,
            $data['internalDatabaseSizes'] ?? null,
            isset($data['embedderRequests'])
                ? BatchEmbedderRequests::fromArray($data['embedderRequests'])
                : null,
        );
    }
}
