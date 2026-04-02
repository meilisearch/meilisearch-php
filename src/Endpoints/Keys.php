<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CreateKeyQuery;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Key;
use Meilisearch\Contracts\KeysQuery;
use Meilisearch\Contracts\KeysResults;
use Meilisearch\Contracts\UpdateKeyQuery;

/**
 * @phpstan-type RawKey array{
 *     uid: non-empty-string,
 *     key: non-empty-string,
 *     actions: list<non-empty-string>,
 *     indexes: list<non-empty-string>,
 *     name?: non-empty-string,
 *     description?: non-empty-string,
 *     expiresAt: non-empty-string|null,
 *     createdAt: non-empty-string,
 *     updatedAt: non-empty-string
 * }
 * @phpstan-type RawKeys array{
 *     results: array<int, RawKey>,
 *     offset: non-negative-int,
 *     limit: non-negative-int,
 *     total: non-negative-int
 * }
 */
class Keys extends Endpoint
{
    protected const PATH = '/keys';

    protected Http $http;

    /**
     * @param non-empty-string $keyOrUid
     */
    public function get(string $keyOrUid): Key
    {
        $response = $this->http->get(self::PATH.'/'.$keyOrUid);

        return Key::fromArray($response);
    }

    public function all(?KeysQuery $options = null): KeysResults
    {
        $query = isset($options) ? $options->toArray() : [];

        $response = $this->allRaw($query);
        $response['results'] = array_map(static fn (array $data) => Key::fromArray($data), $response['results']);

        return new KeysResults($response);
    }

    /**
     * @param array{
     *     limit?: non-negative-int,
     *     offset?: non-negative-int,
     * } $options
     *
     * @return RawKeys
     */
    public function allRaw(array $options = []): array
    {
        return $this->http->get(self::PATH.'/', $options);
    }

    public function create(CreateKeyQuery $request): Key
    {
        $response = $this->http->post(self::PATH, $request->toArray());

        return Key::fromArray($response);
    }

    public function update(UpdateKeyQuery $request): Key
    {
        $response = $this->http->patch(self::PATH.'/'.$request->keyOrUid, $request->toArray());

        return Key::fromArray($response);
    }

    /**
     * @param non-empty-string $keyOrUid
     */
    public function delete(string $keyOrUid): array
    {
        return $this->http->delete(self::PATH.'/'.$keyOrUid) ?? [];
    }
}
