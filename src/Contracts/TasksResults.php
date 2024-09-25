<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TasksResults extends Data
{
    /**
     * @var non-negative-int
     */
    private int $next;

    /**
     * @var non-negative-int
     */
    private int $limit;

    /**
     * @var non-negative-int
     */
    private int $from;

    /**
     * @var non-negative-int
     */
    private int $total;

    public function __construct(array $params)
    {
        parent::__construct($params['results'] ?? []);

        $this->from = $params['from'] ?? 0;
        $this->limit = $params['limit'] ?? 0;
        $this->next = $params['next'] ?? 0;
        $this->total = $params['total'] ?? 0;
    }

    /**
     * @return array<int, array>
     */
    public function getResults(): array
    {
        return $this->data;
    }

    /**
     * @return non-negative-int
     */
    public function getNext(): int
    {
        return $this->next;
    }

    /**
     * @return non-negative-int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return non-negative-int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @return non-negative-int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    public function toArray(): array
    {
        return [
            'results' => $this->data,
            'next' => $this->next,
            'limit' => $this->limit,
            'from' => $this->from,
            'total' => $this->total,
        ];
    }

    public function count(): int
    {
        return \count($this->data);
    }
}
