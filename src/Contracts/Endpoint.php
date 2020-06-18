<?php


namespace MeiliSearch\Contracts;


use MeiliSearch\Endpoints\Health;
use MeiliSearch\Exceptions\HTTPRequestException;
use Psr\Http\Message\ResponseInterface;

abstract class Endpoint
{
    /**
     * @var Http
     */
    protected $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    public function show()
    {
        return $this->http->get(static::PATH);
    }
}