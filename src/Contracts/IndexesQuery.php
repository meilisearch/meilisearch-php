<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

class IndexesQuery
{
    private int $offset;
    private int $limit;

    public function setOffset(int $offset): IndexesQuery
    {
        $this->offset = $offset;

        return $this;
    }

    public function setLimit(int $limit): IndexesQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'offset' => $this->offset ?? null,
            'limit' => $this->limit ?? null,
        ], function ($item) { return null != $item || is_numeric($item); });
    }
}
