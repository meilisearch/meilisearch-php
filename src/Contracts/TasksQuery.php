<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

class TasksQuery
{
    private int $from;
    private int $limit;
    private int $next;
    private array $types;
    private array $statuses;
    private array $indexUids;

    public function setFrom(int $from): TasksQuery
    {
        $this->from = $from;

        return $this;
    }

    public function setLimit(int $limit): TasksQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function setNext(int $next): TasksQuery
    {
        $this->next = $next;

        return $this;
    }

    public function setTypes(array $types): TasksQuery
    {
        $this->types = $types;

        return $this;
    }

    public function setStatuses(array $statuses): TasksQuery
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function setIndexUids(array $indexUids): TasksQuery
    {
        $this->indexUids = $indexUids;

        return $this;
    }

    public function getIndexUids(): array
    {
        return $this->indexUids ?? [];
    }

    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from ?? null,
            'limit' => $this->limit ?? null,
            'next' => $this->next ?? null,
            'statuses' => isset($this->statuses) ? implode(',', $this->statuses) : null,
            'types' => isset($this->types) ? implode(',', $this->types) : null,
            'indexUids' => isset($this->indexUids) ? implode(',', $this->indexUids) : null,
        ], function ($item) { return null != $item || is_numeric($item); });
    }
}
