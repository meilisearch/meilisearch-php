<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

class DeleteTasksQuery
{
    use TasksQueryTrait;

    /**
     * @var non-empty-list<int>|null
     */
    private ?array $canceledBy = null;

    /**
     * @param non-empty-list<int> $canceledBy
     *
     * @return $this
     */
    public function setCanceledBy(array $canceledBy): self
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            array_merge(
                $this->baseArray(),
                ['canceledBy' => $this->formatArray($this->canceledBy)]
            ), static function ($item) { return null !== $item; }
        );
    }
}
