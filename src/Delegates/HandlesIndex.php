<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Indexes;

trait HandlesIndex
{
    /**
     * @return Indexes[]
     */
    public function getAllIndexes(): array
    {
        return $this->index->all();
    }

    public function getAllRawIndexes(): array
    {
        return $this->index->allRaw();
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

    public function deleteAllIndexes(): array
    {
        $tasks = [];
        $indexes = $this->getAllIndexes();
        foreach ($indexes as $index) {
            $tasks[] = $index->delete();
        }

        return $tasks;
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
