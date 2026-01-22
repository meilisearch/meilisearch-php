<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class TasksResults extends Data
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

    /**
     * @param array{
     *     results: array<int, Task>,
     *     from: non-negative-int|null,
     *     limit: non-negative-int,
     *     next: non-negative-int|null,
     *     total: non-negative-int
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params['results']);

        $this->from = $params['from'] ?? 0;
        $this->limit = $params['limit'];
        $this->next = $params['next'] ?? 0;
        $this->total = $params['total'];
    }

    /**
     * @return array<int, Task>
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

    /**
     * @return array{
     *     results: array<int, Task>,
     *     from: non-negative-int,
     *     limit: non-negative-int,
     *     next: non-negative-int,
     *     total: non-negative-int
     * }
     */
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
}
