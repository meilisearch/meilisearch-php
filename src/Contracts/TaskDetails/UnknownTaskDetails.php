<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{}>
 */
final class UnknownTaskDetails implements TaskDetails
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        public readonly array $data,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
