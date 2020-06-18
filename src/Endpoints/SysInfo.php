<?php

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class SysInfo extends Endpoint
{
    const PATH = '/sys-info';

    public function pretty()
    {
        return $this->http->get(self::PATH.'/pretty');
    }
}
