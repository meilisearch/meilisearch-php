<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

/**
 * @final since 1.3.0
 */
class Version extends Endpoint
{
    protected const PATH = '/version';
}
