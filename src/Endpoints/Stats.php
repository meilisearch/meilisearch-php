<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Stats extends Endpoint
{
    protected const PATH = '/stats';

    public function show(array $query = []): ?array
    {
        return $this->http->get(static::PATH, $query);
    }
}
