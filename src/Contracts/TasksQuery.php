<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

class TasksQuery
{
    use TasksQueryTrait;

    private ?int $from = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-empty-list<int>|null
     */
    private ?array $canceledBy = null;

    private ?int $batchUid = null;

    private ?bool $reverse = null;

    /**
     * @return $this
     */
    public function setFrom(int $from): self
    {
        $this->from = $from;

        return $this;
    }

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

    /**
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBatchUid(int $batchUid): self
    {
        $this->batchUid = $batchUid;

        return $this;
    }

    public function setReverse(bool $reverse): self
    {
        $this->reverse = $reverse;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            array_merge(
                $this->baseArray(),
                [
                    'from' => $this->from,
                    'limit' => $this->limit,
                    'canceledBy' => $this->formatArray($this->canceledBy),
                    'batchUid' => $this->batchUid,
                    'reverse' => (null !== $this->reverse ? ($this->reverse ? 'true' : 'false') : null),
                ]
            ),
            static function ($item) {
                return null !== $item;
            }
        );
    }
}
