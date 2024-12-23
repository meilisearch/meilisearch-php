<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Contracts\BatchesResults;
use Meilisearch\Endpoints\Batches;

trait HandlesBatches
{
    protected Batches $batches;

    public function getBatch($uid): array
    {
        return $this->batches->get($uid);
    }

    public function getBatches(?BatchesQuery $options = null): BatchesResults
    {
        $query = null !== $options ? $options->toArray() : [];

        $response = $this->batches->all($query);

        return new BatchesResults($response);
    }
}
