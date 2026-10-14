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
     * @param list<array{non-empty-string, non-empty-string}> $indexes
     */
    public function swapIndexes(array $indexes): Task
    {
        $payload = array_map(static fn (array $pair): array => ['indexes' => $pair], $indexes);

        return Task::fromArray($this->http->post('/swap-indexes', $payload), partial(waitForTask(...), $this->http));
    }
}
