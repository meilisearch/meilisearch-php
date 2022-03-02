<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Endpoints\Keys;

trait HandlesKeys
{
    public function getKeys(): array
    {
        return $this->keys->all();
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
