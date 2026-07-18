<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type RawBatchEmbedderRequests array{
 *     total: non-negative-int,
 *     failed: non-negative-int,
 *     lastError?: non-empty-string|null
 * }
 */
final class BatchEmbedderRequests
{
    /**
     * @param non-negative-int      $total
     * @param non-negative-int      $failed
     * @param non-empty-string|null $lastError
     */
    public function __construct(
        private readonly int $total,
        private readonly int $failed,
        private readonly ?string $lastError = null,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return non-negative-int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * @return non-empty-string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @param RawBatchEmbedderRequests $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['total'],
            $data['failed'],
            $data['lastError'] ?? null,
        );
    }
}
