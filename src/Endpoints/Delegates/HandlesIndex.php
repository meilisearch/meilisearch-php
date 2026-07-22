<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Index;
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
     *
     * @return array{
     *     uid: non-empty-string,
     *     primaryKey: string|null,
     *     createdAt: non-empty-string,
     *     updatedAt: non-empty-string
     * }
     */
    public function getRawIndex(string $uid): array
    {
        return $this->index($uid)->fetchRawInfo();
    }

    /**
     * @param non-empty-string $uid
     */
    public function index(string $uid): Index
    {
        return new Index($this->http, $uid);
    }

    /**
     * @param non-empty-string $uid
     */
    public function getIndex(string $uid): Index
    {
        return $this->index($uid);
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
     * @param array{
     *     primaryKey?: string|null
     * } $options
     */
    public function createIndex(string $uid, array $options = []): Task
    {
        return $this->index->create($uid, $options);
    }

    /**
     * @param non-empty-string $uid
     * @param array{
     *     primaryKey?: string|null,
     *     uid?: non-empty-string
     * } $options
     */
    public function updateIndex(string $uid, array $options = []): Task
    {
        return $this->index($uid)->update($options);
    }

    /**
     * @param non-empty-string $uid
     */
    public function compactIndex(string $uid): Task
    {
        return $this->index($uid)->compact();
    }
}
