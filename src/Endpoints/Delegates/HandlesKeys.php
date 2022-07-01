<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Endpoints\Keys;
use MeiliSearch\Contracts\KeysResults;
use MeiliSearch\Contracts\KeysQuery;

trait HandlesKeys
{
    public function getKeys(KeysQuery $options = null): KeysResults
    {
        return $this->keys->all($options);
    }

    public function getRawKeys(): array
    {
        return $this->keys->allRaw();
    }

    public function getKey($key): Keys
    {
        return $this->keys->get($key);
    }

    public function createKey(array $options = []): Keys
    {
        return $this->keys->create($options);
    }

    public function updateKey(string $key, array $options = []): Keys
    {
        return $this->keys->update($key, $options);
    }

    public function deleteKey(string $key): array
    {
        return $this->keys->delete($key);
    }
}
