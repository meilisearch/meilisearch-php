<?php

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Network extends Endpoint
{
    protected const PATH = '/network';

    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    public function update(array $body): array
    {
        return $this->http->patch(self::PATH, $body);
    }
}
