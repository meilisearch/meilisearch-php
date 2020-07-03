<?php

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\HTTPRequestException;

trait HandlesIndex
{
    public function getAllIndexes(): array
    {
        return $this->index->all();
    }

    public function showIndex($uid): array
    {
        return (new Indexes($this->http, $uid))->show();
    }

    public function deleteIndex($uid): void
    {
        (new Indexes($this->http, $uid))->delete();
    }

    public function deleteAllIndexes(): void
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

    /**
     * @param string $uid
     * @param array $options
     * @return Indexes
     * @throws HTTPRequestException
     */
    public function getOrCreateIndex(string $uid, array $options = []): Indexes
    {
        $index = $this->getIndex($uid);

        try {
            $index = $this->createIndex($uid, $options);
        } catch (HTTPRequestException $e) {
            if (is_array($e->http_body) && 'index_already_exists' !== $e->http_body['errorCode']) {
                throw $e;
            }
        }

        return $index;
    }
}
