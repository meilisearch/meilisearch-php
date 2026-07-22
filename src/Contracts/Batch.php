<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
use Meilisearch\Exceptions\LogicException;

/**
 * @phpstan-import-type RawBatchStats from BatchStats
 * @phpstan-import-type RawBatchProgress from BatchProgress
 *
 * @phpstan-type RawBatch array{
 *     uid: non-negative-int,
 *     details: array<mixed>,
 *     stats: RawBatchStats,
 *     duration: non-empty-string|null,
 *     startedAt: non-empty-string,
 *     finishedAt: non-empty-string|null,
 *     progress: RawBatchProgress|null,
 *     batchStrategy?: non-empty-string|null
 * }
 */
final class Batch implements \ArrayAccess
{
    /**
     * @param non-negative-int      $uid
     * @param non-empty-string|null $duration
     * @param non-empty-string|null $batchStrategy
     * @param RawBatch              $raw
     */
    public function __construct(
        private readonly int $uid,
        private readonly TaskDetails $details,
        private readonly BatchStats $stats,
        private readonly ?string $duration,
        private readonly \DateTimeImmutable $startedAt,
        private readonly ?\DateTimeImmutable $finishedAt,
        private readonly ?BatchProgress $progress,
        private readonly ?string $batchStrategy,
        private readonly array $raw,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    public function getDetails(): TaskDetails
    {
        return $this->details;
    }

    public function getStats(): BatchStats
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
     */
    public function getProgress(): ?BatchProgress
    {
        return $this->progress;
    }

    /**
     * Free-form reason why the batch stopped accepting tasks (not a closed enum).
     *
     * @return non-empty-string|null
     */
    public function getBatchStrategy(): ?string
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
            UnknownTaskDetails::fromArray($data['details']),
            BatchStats::fromArray($data['stats']),
            $data['duration'] ?? null,
            new \DateTimeImmutable($data['startedAt']),
            null !== $data['finishedAt']
                ? new \DateTimeImmutable($data['finishedAt']) : null,
            null !== ($data['progress'] ?? null) ? BatchProgress::fromArray($data['progress']) : null,
            $data['batchStrategy'] ?? null,
            $data,
        );
    }
}
