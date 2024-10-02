<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class MultiSearchFederation
{
    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @param non-negative-int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param non-negative-int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return array{
     *     limit?: non-negative-int,
     *     offset?: non-negative-int
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'offset' => $this->offset,
        ], static function ($item) { return null !== $item; });
    }
}
