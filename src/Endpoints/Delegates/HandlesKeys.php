<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\CreateKeyQuery;
use Meilisearch\Contracts\Key;
use Meilisearch\Contracts\KeysQuery;
use Meilisearch\Contracts\KeysResults;
use Meilisearch\Contracts\UpdateKeyQuery;
use Meilisearch\Endpoints\Keys;

/**
 * @phpstan-import-type RawKeys from Keys
 */
trait HandlesKeys
{
    protected Keys $keys;

    public function getKeys(?KeysQuery $options = null): KeysResults
    {
        return $this->keys->all($options);
    }

    /**
     * @param array{
     *     limit?: non-negative-int,
     *     offset?: non-negative-int,
     * } $options
     *
     * @return RawKeys
     */
    public function getRawKeys(array $options = []): array
    {
        return $this->keys->allRaw($options);
    }

    /**
     * @param non-empty-string $keyOrUid
     */
    public function getKey(string $keyOrUid): Key
    {
        return $this->keys->get($keyOrUid);
    }

    public function createKey(CreateKeyQuery $request): Key
    {
        return $this->keys->create($request);
    }

    public function updateKey(UpdateKeyQuery $request): Key
    {
        return $this->keys->update($request);
    }

    public function deleteKey(string $keyOrUid): array
    {
        return $this->keys->delete($keyOrUid);
    }
}
