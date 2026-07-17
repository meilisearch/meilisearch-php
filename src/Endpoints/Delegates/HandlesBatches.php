<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Batch;
use Meilisearch\Contracts\BatchesQuery;
use Meilisearch\Contracts\BatchesResults;
use Meilisearch\Endpoints\Batches;

trait HandlesBatches
{
    protected Batches $batches;

    public function getBatch(int $uid): Batch
    {
        return $this->batches->get($uid);
    }

    public function getBatches(?BatchesQuery $options = null): BatchesResults
    {
        $query = null !== $options ? $options->toArray() : [];

        $response = $this->batches->all($query);

        /** @var array{
         *     results: array<int, Batch>,
         *     from: non-negative-int|null,
         *     limit: non-negative-int,
         *     next: non-negative-int|null,
         *     total: non-negative-int
         * } $rawResponse
         */
        $rawResponse = $response;

        return new BatchesResults($rawResponse);
    }
}
