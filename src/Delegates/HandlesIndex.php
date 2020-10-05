<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\HTTPRequestException;

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

    public function showIndex(string $uid): array
    {
        return (new Indexes($this->http, $uid))->show();
    }

    public function deleteIndex(string $uid): array
    {
        return (new Indexes($this->http, $uid))->delete();
    }

    public function deleteAllIndexes(): void
    {
        $indexes = $this->getAllIndexes();
        foreach ($indexes as $index) {
            $index->delete();
        }
    }

    public function getIndex(string $uid): Indexes
    {
        return new Indexes($this->http, $uid);
    }

    public function createIndex(string $uid, array $options = []): Indexes
    {
        return $this->index->create($uid, $options);
    }

    public function updateIndex(string $uid, array $options = []): array
    {
        return (new Indexes($this->http, $uid))->update($options);
    }

    /**
     * @throws HTTPRequestException
     */
    public function getOrCreateIndex(string $uid, array $options = []): Indexes
    {
        $index = $this->getIndex($uid);

        try {
            $index = $this->createIndex($uid, $options);
        } catch (HTTPRequestException $e) {
            if (\is_array($e->httpBody) && 'index_already_exists' !== $e->httpBody['errorCode']) {
                throw $e;
            }
        }

        return $index;
    }
}
