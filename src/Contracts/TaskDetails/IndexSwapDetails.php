<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     swaps: array<array{indexes: mixed}>
 * }>
 */
final class IndexSwapDetails implements TaskDetails
{
    /**
     * @param array<array{indexes: mixed}> $swaps
     */
    public function __construct(
        public readonly array $swaps,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['swaps'],
        );
    }
}
