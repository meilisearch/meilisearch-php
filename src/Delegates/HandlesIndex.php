<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;

/**
 * @property Indexes index
 */
trait HandlesIndex
{
    /**
     * @return Indexes[]
     */
    public function getAllIndexes(): array
    {
        return $this->index->all();
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

    public function deleteAllIndexes(): void
    {
        $indexes = $this->getAllIndexes();
        foreach ($indexes as $index) {
            $index->delete();
        }
    }

    public function createIndex(string $uid, array $options = []): Indexes
    {
        return $this->index->create($uid, $options);
    }

    public function updateIndex(string $uid, array $options = []): Indexes
    {
        return $this->index($uid)->update($options);
    }

    /**
     * @throws ApiException
     */
    public function getOrCreateIndex(string $uid, array $options = []): Indexes
    {
        try {
            $index = $this->getIndex($uid);
        } catch (ApiException $e) {
            if (\is_array($e->httpBody) && 'index_not_found' === $e->httpBody['errorCode']) {
                $index = $this->createIndex($uid, $options);
            } else {
                throw $e;
            }
        }

        return $index;
    }
}
