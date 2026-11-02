<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @phpstan-type RawIndexUpdateDetails array{
 *     primaryKey: non-empty-string|null,
 *     oldIndexUid?: non-empty-string,
 *     newIndexUid?: non-empty-string
 * }
 *
 * @implements TaskDetails<RawIndexUpdateDetails>
 */
final class IndexUpdateDetails implements TaskDetails
{
    /**
     * @param non-empty-string|null $primaryKey  Value of the primaryKey field supplied during index update. `null` if it was not specified.
     * @param non-empty-string|null $oldIndexUid Uid of the index before the update. Only set when the update renamed the index (since Meilisearch v1.18.0).
     * @param non-empty-string|null $newIndexUid Uid of the index after the update. Only set when the update renamed the index (since Meilisearch v1.18.0).
     */
    public function __construct(
        public readonly ?string $primaryKey,
        public readonly ?string $oldIndexUid = null,
        public readonly ?string $newIndexUid = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['primaryKey'],
            $data['oldIndexUid'] ?? null,
            $data['newIndexUid'] ?? null,
        );
    }
}
