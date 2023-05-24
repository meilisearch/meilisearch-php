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

    public function setFilter(array $filter): DocumentsQuery
    {
        $this->filter = $filter;

        return $this;
    }

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
