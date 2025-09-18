<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\MultiSearchFederation;
use Meilisearch\Contracts\SearchQuery;

trait HandlesMultiSearch
{
    protected Http $http;

    /**
     * @param list<SearchQuery> $queries
     */
    public function multiSearch(array $queries = [], ?MultiSearchFederation $federation = null)
    {
        $body = [];

        foreach ($queries as $query) {
            $body[] = $query->toArray();
        }

        $payload = ['queries' => $body];
        if (null !== $federation) {
            $payload['federation'] = (object) $federation->toArray();
        }

        return $this->http->post('/multi-search', $payload);
    }
}
