<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @phpstan-type RawIndexSwapDetails array{
 *     swaps: list<array{indexes: array{non-empty-string, non-empty-string}, rename: bool}>
 * }
 *
 * @implements TaskDetails<RawIndexSwapDetails>
 */
final class IndexSwapDetails implements TaskDetails
{
    /**
     * @param list<array{indexes: array{non-empty-string, non-empty-string}, rename: bool}> $swaps
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
