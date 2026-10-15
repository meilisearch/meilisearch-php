<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Exceptions\LogicException;

/**
 * Read-only array access for map-like setting values.
 *
 * Writes throw so callers cannot mutate a response object in place.
 *
 * @phpstan-require-extends Data
 */
trait ReadOnlyArrayAccess
{
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('This object is immutable.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || \array_key_exists($offset, $this->data);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('This object is immutable.');
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }
}
