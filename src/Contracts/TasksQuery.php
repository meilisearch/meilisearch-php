<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Delegates\TasksQueryTrait;

class TasksQuery
{
    use TasksQueryTrait;

    private int $from;
    private int $limit;

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

    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from ?? null,
            'limit' => $this->limit ?? null,
            'next' => $this->next ?? null,
            'beforeEnqueuedAt' => $this->formatDate($this->beforeEnqueuedAt ?? null),
            'afterEnqueuedAt' => $this->formatDate($this->afterEnqueuedAt ?? null),
            'beforeStartedAt' => $this->formatDate($this->beforeStartedAt ?? null),
            'afterStartedAt' => $this->formatDate($this->afterStartedAt ?? null),
            'beforeFinishedAt' => $this->formatDate($this->beforeFinishedAt ?? null),
            'afterFinishedAt' => $this->formatDate($this->afterFinishedAt ?? null),
            'statuses' => $this->formatArray($this->statuses ?? null),
            'uids' => $this->formatArray($this->uids ?? null),
            'canceledBy' => $this->formatArray($this->canceledBy ?? null),
            'types' => $this->formatArray($this->types ?? null),
            'indexUids' => $this->formatArray($this->indexUids ?? null),
        ], function ($item) { return null != $item || is_numeric($item); });
    }
}
