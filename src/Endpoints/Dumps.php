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

    public function status(string $uid): array
    {
        return $this->http->get(self::PATH.'/'.$uid.'/status');
    }
}
