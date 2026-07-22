<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Batch;
use Meilisearch\Contracts\Endpoint;

/**
 * @phpstan-import-type RawBatch from Batch
 *
 * @phpstan-type RawBatches array{
 *     results: list<RawBatch>,
 *     from: non-negative-int|null,
 *     limit: non-negative-int,
 *     next: non-negative-int|null,
 *     total: non-negative-int
 * }
 * @phpstan-type BatchesResponse array{
 *     results: list<Batch>,
 *     from: non-negative-int|null,
 *     limit: non-negative-int,
 *     next: non-negative-int|null,
 *     total: non-negative-int
 * }
 */
class Batches extends Endpoint
{
    protected const PATH = '/batches';

    public function get(int $batchUid): Batch
    {
        /** @var RawBatch $rawBatch */
        $rawBatch = $this->http->get(self::PATH.'/'.$batchUid);

        return Batch::fromArray($rawBatch);
    }

    /**
     * @return BatchesResponse
     */
    public function all(array $query = []): array
    {
        $rawData = $this->http->get(self::PATH.'/', $query);
        /** @var RawBatches $rawBatches */
        $rawBatches = $rawData;
        $results = array_map(
            static fn (array $batch): Batch => Batch::fromArray($batch),
            $rawBatches['results'],
        );

        return [
            'results' => $results,
            'from' => $rawBatches['from'],
            'limit' => $rawBatches['limit'],
            'next' => $rawBatches['next'],
            'total' => $rawBatches['total'],
        ];
    }
}
