<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Endpoints\Delegates\TasksQueryTrait;

class BatchesQuery
{
    use TasksQueryTrait;

    private ?int $from = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

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
                    'reverse' => (null !== $this->reverse ? ($this->reverse ? 'true' : 'false') : null),
                ]
            ),
            static function ($item) {
                return null !== $item;
            }
        );
    }
}
