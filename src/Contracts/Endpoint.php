<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

abstract class Endpoint
{
    protected const PATH = '';

    /**
     * @var Http
     */
    protected $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    public function show(): ?array
    {
        return $this->http->get(static::PATH);
    }
}
