<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class Version extends Endpoint
{
    protected const PATH = '/version';
}
