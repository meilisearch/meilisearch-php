<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class Health
{
    public function __construct(
        private readonly HealthStatus $status,
    ) {
    }

    public function getStatus(): HealthStatus
    {
        return $this->status;
    }

    /**
     * @param array{status: non-empty-string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            HealthStatus::tryFrom($data['status']) ?? HealthStatus::Unknown,
        );
    }
}
