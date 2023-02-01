<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

class TasksQuery
{
    use TasksQueryTrait;

    private int $from;
    private int $limit;
    private array $canceledBy;

    public function setFrom(int $from): TasksQuery
    {
        $this->from = $from;

        return $this;
    }

    public function setCanceledBy(array $canceledBy): TasksQuery
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function setLimit(int $limit): TasksQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            array_merge(
                $this->baseArray(),
                [
                    'from' => $this->from ?? null,
                    'limit' => $this->limit ?? null,
                    'canceledBy' => $this->formatArray($this->canceledBy ?? null),
                ]
            ), function ($item) { return null != $item || is_numeric($item); }
        );
    }
}
