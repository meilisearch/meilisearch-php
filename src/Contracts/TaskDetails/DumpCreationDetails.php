<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     dumpUid: non-empty-string|null
 * }>
 */
final class DumpCreationDetails implements TaskDetails
{
    /**
     * @param non-empty-string|null $dumpUid
     */
    public function __construct(
        public readonly ?string $dumpUid,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['dumpUid'],
        );
    }
}
