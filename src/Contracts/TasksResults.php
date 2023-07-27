<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TasksResults extends Data
{
    /**
     * @var int<0, max>
     */
    private int $next;
    /**
     * @var int<0, max>
     */
    private int $limit;
    /**
     * @var int<0, max>
     */
    private int $from;
    /**
     * @var int<0, max>
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

    public function getNext(): int
    {
        return $this->next;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

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
