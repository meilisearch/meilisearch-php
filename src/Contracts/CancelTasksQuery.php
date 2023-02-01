<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

class CancelTasksQuery
{
    use TasksQueryTrait;

    private array $canceledBy;

    public function setCanceledBy(array $canceledBy)
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            array_merge(
                $this->baseArray(),
                ['canceledBy' => $this->formatArray($this->canceledBy ?? null)]
            ), function ($item) { return null != $item || is_numeric($item); }
        );
    }
}
