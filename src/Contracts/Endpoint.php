<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

abstract class Endpoint
{
    protected const PATH = '';
    protected Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }
}
