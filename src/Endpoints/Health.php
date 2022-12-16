<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Health extends Endpoint
{
    protected const PATH = '/health';
}
