<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class Data implements \Countable, \IteratorAggregate
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

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->getIterator()->getArrayCopy();
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
