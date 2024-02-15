<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Endpoints\Indexes;

trait HandlesIndex
{
    protected Indexes $index;

    public function getIndexes(?IndexesQuery $options = null): IndexesResults
    {
        return $this->index->all($options ?? null);
    }

    public function getRawIndex(string $uid): array
    {
        return $this->index($uid)->fetchRawInfo();
    }

    public function index(string $uid): Indexes
    {
        return new Indexes($this->http, $uid);
    }

    public function getIndex(string $uid): Indexes
    {
        return $this->index($uid)->fetchInfo();
    }

    public function deleteIndex(string $uid): array
    {
        return $this->index($uid)->delete();
    }

    public function createIndex(string $uid, array $options = []): array
    {
        return $this->index->create($uid, $options);
    }

    public function updateIndex(string $uid, array $options = []): array
    {
        return $this->index($uid)->update($options);
    }
}
