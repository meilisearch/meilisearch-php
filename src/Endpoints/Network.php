<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\NetworkResults;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
 */
class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * @param array{
     *     self?: non-empty-string,
     *     remotes?: array<non-empty-string, RemoteConfig>
     * } $body
     *
     * @return array{
     *     self: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>
     * }
     */
    public function update(array $body): array
    {
        return $this->http->patch(self::PATH, $body);
    }
}
