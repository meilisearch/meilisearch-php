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
     * @param array|string $ids Array of document IDs or comma-separated string of IDs
     */
    public function setIds($ids): self
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
     * @return array{
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     fields?: non-empty-list<string>|non-empty-string,
     *     filter?: list<non-empty-string|list<non-empty-string>>,
     *     retrieveVectors?: 'true'|'false',
     *     ids?: string
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'offset' => $this->offset,
            'limit' => $this->limit,
            'fields' => $this->getFields(),
            'filter' => $this->filter,
            'retrieveVectors' => (null !== $this->retrieveVectors ? ($this->retrieveVectors ? 'true' : 'false') : null),
            'ids' => \is_array($this->ids) ? implode(',', $this->ids) : $this->ids,
        ], static function ($item) { return null !== $item; });
    }

    /**
     * Prepares fields for request
     * Fix for 1.2 document/fetch.
     *
     * @see https://github.com/meilisearch/meilisearch-php/issues/522
     *
     * @return array|string|null
     */
    private function getFields()
    {
        if (null === $this->fields) {
            return null;
        }

        if (null !== $this->filter) {
            return $this->fields;
        }

        return implode(',', $this->fields);
    }
}
