<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

class TasksResults extends Data
{
    private int $next;
    private int $limit;
    private int $from;

    public function __construct(array $params)
    {
        parent::__construct($params['results'] ?? []);

        $this->from = $params['from'] ?? 0;
        $this->limit = $params['limit'] ?? 0;
        $this->next = $params['next'] ?? 0;
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

    public function toArray(): array
    {
        return [
            'results' => $this->data,
            'next' => $this->next,
            'limit' => $this->limit,
            'from' => $this->from,
        ];
    }

    public function count(): int
    {
        return \count($this->data);
    }
}
