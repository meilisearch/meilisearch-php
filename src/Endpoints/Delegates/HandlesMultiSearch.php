<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\MultiSearchFederation;

trait HandlesMultiSearch
{
    protected Http $http;

    /**
     * @param list<\Meilisearch\Contracts\SearchQuery> $queries
     */
    public function multiSearch(array $queries = [], ?MultiSearchFederation $federation = null)
    {
        $body = [];

        foreach ($queries as $query) {
            $body[] = $query->toArray();
        }

        return $this->http->post('/multi-search', ['queries' => $body, 'federation' => $federation->toArray()]);
    }
}
