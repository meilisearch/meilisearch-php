<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @template T of array
 *
 * @since Meilisearch v1.19.0
 */
interface TaskNetwork
{
    /**
     * @param T $data
     */
    public static function fromArray(array $data): self;
}
