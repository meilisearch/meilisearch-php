<?php

declare(strict_types=1);

namespace Meilisearch;

/**
 * @internal
 */
function partial(callable $func, ...$boundArgs): \Closure
{
    return static function (...$remainingArgs) use ($func, $boundArgs) {
        return $func(...array_merge($boundArgs, $remainingArgs));
    };
}
