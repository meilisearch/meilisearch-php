<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;
use function Meilisearch\waitForTask;

final class Indexes extends Endpoint
{
    protected const PATH = '/indexes';

    /**
     * @param array{
     *     primaryKey?: string|null
     * } $options
     *
     * @throws \Exception
     */
    public function create(string $uid, array $options = []): Task
    {
        $options['uid'] = $uid;

        return Task::fromArray($this->http->post(self::PATH, $options), partial(waitForTask(...), $this->http));
    }

    public function all(?IndexesQuery $options = null): IndexesResults
    {
        $indexes = [];
        $query = isset($options) ? $options->toArray() : [];
        $response = $this->allRaw($query);

        foreach ($response['results'] as $index) {
            $indexes[] = Index::fromArray($index, $this->http);
        }

        $response['results'] = $indexes;

        return new IndexesResults($response);
    }

    /**
     * @param array{
     *     offset?: int,
     *     limit?: int
     * } $options
     *
     * @return array{
     *     results: list<array{
     *         uid: non-empty-string,
     *         primaryKey: string|null,
     *         createdAt: non-empty-string,
     *         updatedAt: non-empty-string
     *     }>,
     *     offset: int,
     *     limit: int,
     *     total: int
     * }
     */
    public function allRaw(array $options = []): array
    {
        return $this->http->get(self::PATH, $options);
    }

    /**
     * Each entry is either a pair of index uids to swap, or an array with the pair under `indexes`
     * and an optional `rename` flag (since Meilisearch v1.18.0) that renames the first index to the
     * second uid instead of swapping them.
     *
     * @param list<array{non-empty-string, non-empty-string}|array{indexes: array{non-empty-string, non-empty-string}, rename?: bool}> $indexes
     *
     * @see https://www.meilisearch.com/docs/reference/api/indexes/swap-indexes
     */
    public function swapIndexes(array $indexes): Task
    {
        $payload = array_map(
            static fn (array $swap): array => \array_key_exists('indexes', $swap) ? $swap : ['indexes' => $swap],
            $indexes,
        );

        return Task::fromArray($this->http->post('/swap-indexes', $payload), partial(waitForTask(...), $this->http));
    }

    /**
     * Rename an index instead of swapping it.
     *
     * @param non-empty-string $fromUid
     * @param non-empty-string $toUid
     *
     * @see https://www.meilisearch.com/docs/reference/api/indexes/swap-indexes
     */
    public function renameIndex(string $fromUid, string $toUid): Task
    {
        return $this->swapIndexes([['indexes' => [$fromUid, $toUid], 'rename' => true]]);
    }
}
