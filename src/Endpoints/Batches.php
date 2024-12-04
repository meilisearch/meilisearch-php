<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

class Batches extends Endpoint
{
    protected const PATH = '/batches';

    public function get($batchUid): array
    {
        return $this->http->get(self::PATH.'/'.$batchUid);
    }

    public function all(array $query = []): array
    {
        return $this->http->get(self::PATH.'/', $query);
    }
}
