<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

/**
 * @final since 1.3.0
 */
class Dumps extends Endpoint
{
    protected const PATH = '/dumps';

    public function create(): array
    {
        return $this->http->post(self::PATH);
    }
}
