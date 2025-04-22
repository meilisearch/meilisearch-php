<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self: string,
     *     remotes: array<string, array{url: string, searchApiKey: string}>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * @param array{
     *     self?: string,
     *     remotes?: array<string, array{url: string, searchApiKey: string}>
     * } $body
     *
     * @return array{
     *     self: string,
     *     remotes: array<string, array{url: string, searchApiKey: string}>
     * }
     */
    public function update(array $body): array
    {
        return $this->http->patch(self::PATH, $body);
    }
}
