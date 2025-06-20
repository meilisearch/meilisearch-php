<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @template T of array
 */
interface TaskDetails
{
    /**
     * @param T $data
     */
    public static function fromArray(array $data): self;
}
