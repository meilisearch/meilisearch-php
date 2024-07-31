<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Http;

trait HandlesMultiSearch
{
    protected Http $http;

    /**
     * @param list<\Meilisearch\Contracts\SearchQuery> $queries
     */
    public function multiSearch(array $queries = [], array $multiSearchParams = [])
    {
        $body = [];

        foreach ($queries as $query) {
            $body[] = $query->toArray();
        }

        $payload = array_merge(['queries' => $body], $multiSearchParams);

        return $this->http->post('/multi-search', $payload);
    }
}
