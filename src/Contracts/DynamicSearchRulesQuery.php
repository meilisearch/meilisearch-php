<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class DynamicSearchRulesQuery
{
    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    private ?DynamicSearchRulesFilter $filter = null;

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
     * @return $this
     */
    public function setFilter(DynamicSearchRulesFilter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return array{
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     filter?: array{
     *         attributePatterns?: list<non-empty-string>,
     *         active?: bool
     *     }
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'offset' => $this->offset,
            'limit' => $this->limit,
            'filter' => null !== $this->filter ? $this->filter->toArray() : null,
        ], static function ($item) { return null !== $item; });
    }
}
