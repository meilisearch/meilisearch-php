<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

use Meilisearch\Contracts\TaskNetwork;

/**
 * @phpstan-import-type RawTaskOrigin from TaskOrigin
 *
 * @phpstan-type RawOriginNetwork array{
 *     origin: RawTaskOrigin
 * }
 *
 * @implements TaskNetwork<RawOriginNetwork>
 */
final class OriginNetwork implements TaskNetwork
{
    public function __construct(
        public readonly TaskOrigin $origin,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            origin: TaskOrigin::fromArray($data['origin']),
        );
    }
}
