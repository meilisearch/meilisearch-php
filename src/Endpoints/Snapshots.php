<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Snapshots extends Endpoint
{
    protected const PATH = '/snapshots';

    public function create(): array
    {
        return $this->http->post(self::PATH);
    }
}
