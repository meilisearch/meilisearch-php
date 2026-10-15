<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Version extends Endpoint
{
    protected const PATH = '/version';

    public function show(): ?array
    {
        return $this->http->get(self::PATH);
    }
}
