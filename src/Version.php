<?php

namespace MeiliSearch;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\TimeOutException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Version extends Endpoint
{
    const PATH = '/version';

    /**
     * @var Http
     */
    private $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    public function show()
    {
        return $this->http->get(self::PATH);
    }
}
