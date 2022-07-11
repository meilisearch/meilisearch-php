<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

class TasksQuery
{
    private int $from;
    private int $limit;
    private int $next;
    private array $type;
    private array $status;
    private array $indexUid;

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
        $this->type = $types;

        return $this;
    }

    public function setStatus(array $status): TasksQuery
    {
        $this->status = $status;

        return $this;
    }

    public function setUid(array $indexUid): TasksQuery
    {
        $this->indexUid = $indexUid;

        return $this;
    }

    public function getUid(): array
    {
        return $this->indexUid ?? [];
    }

    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from ?? null,
            'limit' => $this->limit ?? null,
            'next' => $this->next ?? null,
            'status' => isset($this->status) ? implode(',', $this->status) : null,
            'type' => isset($this->type) ? implode(',', $this->type) : null,
            'indexUid' => isset($this->indexUid) ? implode(',', $this->indexUid) : null,
        ], function ($item) { return null != $item || is_numeric($item); });
    }
}
