<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class MultiSearchFederation
{
    private ?int $limit = null;
    private ?int $offset = null;

    public function setLimit(int $limit): MultiSearchFederation
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset): MultiSearchFederation
    {
        $this->offset = $offset;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'offset' => $this->offset,
        ], static function ($item) { return null !== $item; });
    }
}
