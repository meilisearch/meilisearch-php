<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

abstract class Endpoint
{
    protected const PATH = '';
    protected Http $http;
    protected ?string $apiKey;

    public function __construct(Http $http, ?string $apiKey = null)
    {
        $this->http = $http;
        $this->apiKey = $apiKey;
    }

    public function show(): ?array
    {
        return $this->http->get(static::PATH);
    }
}
