<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self: non-empty-string,
     *     remotes: array<non-empty-string, array{url: non-empty-string, searchApiKey: string, writeApiKey: string}>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * @param array{
     *     self?: non-empty-string,
     *     remotes?: array<non-empty-string, array{url: non-empty-string, searchApiKey: string, writeApiKey: string}>
     * } $body
     *
     * @return array{
     *     self: non-empty-string,
     *     remotes: array<non-empty-string, array{url: non-empty-string, searchApiKey: string, writeApiKey: string}>
     * }
     */
    public function update(array $body): array
    {
        return $this->http->patch(self::PATH, $body);
    }
}
