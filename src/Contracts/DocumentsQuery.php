<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class DocumentsQuery
{
    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-empty-list<string>|null
     */
    private ?array $fields = null;

    /**
     * @var list<non-empty-string|list<non-empty-string>>|null
     */
    private ?array $filter = null;

    private ?bool $retrieveVectors = null;

    /**
     * @var list<non-empty-string|int>|null
     */
    private ?array $ids = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $sort = null;

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
     * @param non-empty-list<string> $fields
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Sets the filter for the DocumentsQuery.
     *
     * @param list<non-empty-string|list<non-empty-string>> $filter a filter expression written as an array of strings
     *
     * @return $this
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param bool|null $retrieveVectors boolean value to show _vector details
     *
     * @return $this
     */
    public function setRetrieveVectors(?bool $retrieveVectors): self
    {
        $this->retrieveVectors = $retrieveVectors;

        return $this;
    }

    /**
     * @param list<non-empty-string|int> $ids Array of document IDs
     *
     * @return $this
     */
    public function setIds(array $ids): self
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * Checks if the $filter attribute has been set.
     *
     * @return bool true when filter contains at least a non-empty array
     */
    public function hasFilter(): bool
    {
        return null !== $this->filter;
    }

    /**
     * @param list<non-empty-string> $sort
     */
    public function setSort(array $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return array{
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     fields?: non-empty-list<string>|non-empty-string,
     *     filter?: list<non-empty-string|list<non-empty-string>>,
     *     retrieveVectors?: bool,
     *     ids?: string,
     *     sort?: non-empty-list<string>,
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'offset' => $this->offset,
            'limit' => $this->limit,
            'fields' => $this->fields,
            'filter' => $this->filter,
            'retrieveVectors' => $this->retrieveVectors,
            'ids' => ($this->ids ?? []) !== [] ? implode(',', $this->ids) : null,
            'sort' => $this->sort,
        ], static function ($item) { return null !== $item; });
    }
}
