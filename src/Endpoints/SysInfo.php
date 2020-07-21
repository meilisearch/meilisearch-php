<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class SysInfo extends Endpoint
{
    protected const PATH = '/sys-info';

    public function pretty(): array
    {
        return $this->http->get(self::PATH.'/pretty');
    }
}
