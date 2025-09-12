<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class ChatWorkspacesResults extends Data
{
    /**
     * @var non-negative-int
     */
    private int $offset;

    /**
     * @var non-negative-int
     */
    private int $limit;

    /**
     * @var non-negative-int
     */
    private int $total;

    public function __construct(array $params)
    {
        parent::__construct($params['results']);

        $this->offset = $params['offset'];
        $this->limit = $params['limit'];
        $this->total = $params['total'];
    }

    /**
     * @return array<int, array{uid: string}>
     */
    public function getResults(): array
    {
        return $this->data;
    }

    /**
     * @return non-negative-int
     */
    public function getOffset(): int
    {
        return $this->offset;
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
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array{
     *     results: array,
     *     offset: non-negative-int,
     *     limit: non-negative-int,
     *     total: non-negative-int
     * }
     */
    public function toArray(): array
    {
        return [
            'results' => $this->data,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'total' => $this->total,
        ];
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return \count($this->data);
    }
}
