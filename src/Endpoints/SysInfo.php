<?php

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\TimeOutException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SysInfo extends Endpoint
{
    const PATH = '/sys-info';

    public function pretty()
    {
        return $this->http->get(self::PATH . '/pretty');
    }
}
