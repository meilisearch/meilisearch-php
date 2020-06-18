<?php


namespace MeiliSearch\Contracts;


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