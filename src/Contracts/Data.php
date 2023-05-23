<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class Data implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]) || \array_key_exists($offset, $this->data);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
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
