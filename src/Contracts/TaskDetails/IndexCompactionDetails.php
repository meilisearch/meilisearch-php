<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     preCompactionSize: non-empty-string,
 *     postCompactionSize: non-empty-string
 * }>
 */
final class IndexCompactionDetails implements TaskDetails
{
    /**
     * @param non-empty-string $preCompactionSize
     * @param non-empty-string $postCompactionSize
     */
    public function __construct(
        public readonly string $preCompactionSize,
        public readonly string $postCompactionSize,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['preCompactionSize'],
            $data['postCompactionSize'],
        );
    }
}
