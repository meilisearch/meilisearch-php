<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @phpstan-type RawDumpCreationDetails array{
 *     dumpUid: non-empty-string|null
 * }
 *
 * @implements TaskDetails<RawDumpCreationDetails>
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

    public static function fromNullableArray(?array $data): ?self
    {
        if (null === $data || [] === $data) {
            return null;
        }

        /* @var RawDumpCreationDetails $data */
        return self::fromArray($data);
    }
}
