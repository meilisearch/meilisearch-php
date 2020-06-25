<?php

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Indexes;

trait handlesIndex
{
    public function getAllIndexes(): array
    {
        return $this->index->all();
    }

    public function showIndex($uid): array
    {
        return (new Indexes($this->http, $uid))->show();
    }

    public function deleteIndex($uid)
    {
        return (new Indexes($this->http, $uid))->delete();
    }

    public function deleteAllIndexes()
    {
        $indexes = $this->getAllIndexes();
        foreach ($indexes as $index) {
            $index->delete();
        }
    }

    public function getIndex($uid): Indexes
    {
        return new Indexes($this->http, $uid);
    }

    public function createIndex($uid, $options = []): Indexes
    {
        return $this->index->create($uid, $options);
    }
}
