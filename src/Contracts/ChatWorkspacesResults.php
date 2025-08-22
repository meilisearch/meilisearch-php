<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class ChatWorkspacesResults extends Data
{
    private int $offset;
    private int $limit;
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

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function toArray(): array
    {
        return [
            'results' => $this->data,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'total' => $this->total,
        ];
    }

    public function count(): int
    {
        return \count($this->data);
    }
}
