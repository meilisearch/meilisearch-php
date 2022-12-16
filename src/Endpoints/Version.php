<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Version extends Endpoint
{
    protected const PATH = '/version';
}
