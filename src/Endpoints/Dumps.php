<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class Dumps extends Endpoint
{
    protected const PATH = '/dumps';

    public function create(): array
    {
        return $this->http->post(self::PATH);
    }
}
