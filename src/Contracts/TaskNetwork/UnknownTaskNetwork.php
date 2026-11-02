<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

use Meilisearch\Contracts\TaskNetwork;

/**
 * @implements TaskNetwork<array<mixed>>
 */
final class UnknownTaskNetwork implements TaskNetwork
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
