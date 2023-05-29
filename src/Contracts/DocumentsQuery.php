<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class DocumentsQuery
{
    private int $offset;
    private int $limit;
    private array $fields;
    private array $filter;

    public function setOffset(int $offset): DocumentsQuery
    {
        $this->offset = $offset;

        return $this;
    }

    public function setLimit(int $limit): DocumentsQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function setFields(array $fields): DocumentsQuery
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Sets the filter for the DocumentsQuery.
     *
     * @param list<non-empty-string|list<non-empty-string>> $filter a filter expression written as an array of strings
     *
     * @return DocumentsQuery the updated DocumentsQuery instance
     */
    public function setFilter(array $filter): DocumentsQuery
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Checks if the $filter attribute has been set.
     *
     * @return bool true when filter contains at least a non-empty array
     */
    public function hasFilter(): bool
    {
        return isset($this->filter);
    }

    public function toArray(): array
    {
        return array_filter([
            'offset' => $this->offset ?? null,
            'limit' => $this->limit ?? null,
            'filter' => $this->filter ?? null,
            'fields' => isset($this->fields) ? implode(',', $this->fields) : null,
        ], function ($item) { return null != $item || is_numeric($item); });
    }
}
