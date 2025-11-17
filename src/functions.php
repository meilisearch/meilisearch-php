<?php

declare(strict_types=1);

namespace Meilisearch;

/**
 * @internal
 *
 * Creates a partially applied function by binding initial arguments to the given callable.
 *
 * Returns a Closure that, when invoked, calls the original callable with the bound arguments prepended
 * to any new ones.
 *
 * Used internally to build reusable “waiter” functions (e.g., binding the HTTP client to
 * task-waiting logic) and reduce repetitive argument passing.
 */
function partial(callable $func, ...$boundArgs): \Closure
{
    return static function (...$remainingArgs) use ($func, $boundArgs) {
        return $func(...array_merge($boundArgs, $remainingArgs));
    };
}
