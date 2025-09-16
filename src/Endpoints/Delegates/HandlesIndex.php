<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Indexes;

trait HandlesIndex
{
    protected Indexes $index;

    public function getIndexes(?IndexesQuery $options = null): IndexesResults
    {
        return $this->index->all($options ?? null);
    }

    /**
     * @param non-empty-string $uid
     */
    public function getRawIndex(string $uid): array
    {
        return $this->index($uid)->fetchRawInfo();
    }

    /**
     * @param non-empty-string $uid
     */
    public function index(string $uid): Indexes
    {
        return new Indexes($this->http, $uid);
    }

    /**
     * @param non-empty-string $uid
     */
    public function getIndex(string $uid): Indexes
    {
        return $this->index($uid)->fetchInfo();
    }

    /**
     * @param non-empty-string $uid
     */
    public function deleteIndex(string $uid): Task
    {
        return $this->index($uid)->delete();
    }

    /**
     * @param non-empty-string $uid
     */
    public function createIndex(string $uid, array $options = []): Task
    {
        return $this->index->create($uid, $options);
    }

    /**
     * @param non-empty-string $uid
     */
    public function updateIndex(string $uid, array $options = []): Task
    {
        return $this->index($uid)->update($options);
    }
}
