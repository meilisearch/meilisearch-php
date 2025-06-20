<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     primaryKey: non-empty-string|null
 * }>
 */
final class IndexUpdateDetails implements TaskDetails
{
    /**
     * @param non-empty-string|null $primaryKey Value of the primaryKey field supplied during index creation. `null` if it was not specified.
     */
    public function __construct(
        public readonly ?string $primaryKey,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['primaryKey'],
        );
    }
}
