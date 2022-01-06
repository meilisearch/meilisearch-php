<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Endpoints\Keys;

/**
 * @property Keys index
 */
trait HandlesKeys
{
    public function getKeys(): array
    {
        return $this->keys->all();
    }

    public function getKey($key): array
    {
        return $this->keys->get($key);
    }

    public function createKey(array $options = []): array
    {
        return $this->keys->create($options);
    }

    public function updateKey(string $key, array $options = []): array
    {
        return $this->keys->update($key, $options);
    }

    public function deleteKey(string $key): array
    {
        return $this->keys->delete($key) ?? [];
    }
}
