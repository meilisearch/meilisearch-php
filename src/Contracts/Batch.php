<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Exceptions\LogicException;

/**
 * @phpstan-type RawBatchStats array{
 *     totalNbTasks: non-negative-int,
 *     status: array<non-empty-string, non-negative-int>,
 *     types: array<non-empty-string, non-negative-int>,
 *     indexUids: array<non-empty-string, non-negative-int>,
 *     progressTrace?: array<string, mixed>,
 *     writeChannelCongestion?: array<string, mixed>|null,
 *     internalDatabaseSizes?: array<string, mixed>,
 *     embedderRequests?: array{
 *         total: non-negative-int,
 *         failed: non-negative-int,
 *         lastError?: non-empty-string|null
 *     }
 * }
 * @phpstan-type RawBatchProgressStep array{
 *     currentStep: non-empty-string,
 *     finished: non-negative-int,
 *     total: non-negative-int
 * }
 * @phpstan-type RawBatchProgress array{
 *     steps: list<RawBatchProgressStep>,
 *     percentage: float
 * }
 * @phpstan-type RawBatch array{
 *     uid: non-negative-int,
 *     details: array<mixed>,
 *     stats: RawBatchStats,
 *     duration?: non-empty-string|null,
 *     startedAt: non-empty-string,
 *     finishedAt?: non-empty-string|null,
 *     progress?: RawBatchProgress|null,
 *     batchStrategy?: non-empty-string
 * }
 */
final class Batch implements \ArrayAccess
{
    /**
     * @param non-negative-int      $uid
     * @param array<mixed>          $details
     * @param RawBatchStats         $stats
     * @param non-empty-string|null $duration
     * @param RawBatchProgress|null $progress
     * @param non-empty-string      $batchStrategy
     * @param array<mixed>          $raw
     */
    public function __construct(
        private readonly int $uid,
        private readonly array $details,
        private readonly array $stats,
        private readonly ?string $duration,
        private readonly \DateTimeImmutable $startedAt,
        private readonly ?\DateTimeImmutable $finishedAt,
        private readonly ?array $progress,
        private readonly string $batchStrategy,
        private readonly array $raw = [],
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @return array<mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @return RawBatchStats
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * @return non-empty-string|null
     */
    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    /**
     * Real-time progress while the batch is processing; null when finished.
     * When present, `percentage` is documented by Meilisearch as 0.0–100.0.
     *
     * @return RawBatchProgress|null
     */
    public function getProgress(): ?array
    {
        return $this->progress;
    }

    /**
     * Free-form reason why the batch stopped accepting tasks (not a closed enum).
     *
     * @return non-empty-string
     */
    public function getBatchStrategy(): string
    {
        return $this->batchStrategy;
    }

    /**
     * @return RawBatch
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('The Batch object is immutable.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->raw);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('The Batch object is immutable.');
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->raw[$offset] ?? null;
    }

    /**
     * @param RawBatch $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uid'],
            $data['details'],
            $data['stats'],
            $data['duration'] ?? null,
            new \DateTimeImmutable($data['startedAt']),
            \array_key_exists('finishedAt', $data) && null !== $data['finishedAt']
                ? new \DateTimeImmutable($data['finishedAt']) : null,
            $data['progress'] ?? null,
            $data['batchStrategy'] ?? 'unspecified',
            $data,
        );
    }
}
