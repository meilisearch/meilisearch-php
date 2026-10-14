<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Health extends Endpoint
{
    protected const PATH = '/health';

    public function show(): ?array
    {
        return $this->http->get(self::PATH);
    }
}
