<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\BatchesResults;
use Meilisearch\Contracts\Http;
use Meilisearch\Endpoints\Batches;
use PHPUnit\Framework\TestCase;

final class BatchesEndpointTest extends TestCase
{
    public function testAllReturnsBatchesResults(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('get')
            ->with('/batches/', ['limit' => 15])
            ->willReturn([
                'results' => [[
                    'uid' => 9,
                    'details' => ['receivedDocuments' => 1],
                    'stats' => [
                        'totalNbTasks' => 1,
                        'status' => ['succeeded' => 1],
                        'types' => ['documentAdditionOrUpdate' => 1],
                        'indexUids' => ['movies' => 1],
                    ],
                    'duration' => 'PT1S',
                    'startedAt' => '2025-04-09T10:28:12.236789Z',
                    'finishedAt' => '2025-04-09T10:28:13.236789Z',
                    'progress' => null,
                ]],
                'from' => 3,
                'limit' => 15,
                'next' => 18,
                'total' => 50,
            ]);

        $results = (new Batches($http))->all(['limit' => 15]);

        self::assertInstanceOf(BatchesResults::class, $results);
        self::assertSame(3, $results->getFrom());
        self::assertSame(15, $results->getLimit());
        self::assertSame(18, $results->getNext());
        self::assertSame(50, $results->getTotal());
        self::assertSame(9, $results->getResults()[0]->getUid());
    }
}
