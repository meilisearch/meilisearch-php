<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

trait HandlesMultiSearch
{
    /**
     * @param list<SearchQuery> $queries
     */
    public function multiSearch(array $queries = [])
    {
        $body = [];

        foreach ($queries as $query) {
            $body[] = $query->toArray();
        }

        return $this->http->post('/multi-search', ['queries' => $body]);
    }
}
