<?php

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\TimeOutException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Health extends Endpoint
{
    const PATH = '/health';
}
