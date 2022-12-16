<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Dumps extends Endpoint
{
    protected const PATH = '/dumps';

    public function create(): array
    {
        return $this->http->post(self::PATH);
    }
}
