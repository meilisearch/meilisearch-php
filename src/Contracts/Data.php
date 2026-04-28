<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class Data implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var array<mixed>
     */
    protected array $data = [];

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || \array_key_exists($offset, $this->data);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }
}
