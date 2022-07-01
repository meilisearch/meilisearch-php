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

    public function getKey($keyOrUid): Keys
    {
        return $this->keys->get($keyOrUid);
    }

    public function createKey(array $options = []): Keys
    {
        return $this->keys->create($options);
    }

    public function updateKey(string $keyOrUid, array $options = []): Keys
    {
        return $this->keys->update($keyOrUid, $options);
    }

    public function deleteKey(string $keyOrUid): array
    {
        return $this->keys->delete($keyOrUid);
    }
}
