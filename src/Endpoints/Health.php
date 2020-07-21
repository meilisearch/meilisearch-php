<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class Health extends Endpoint
{
    protected const PATH = '/health';
}
