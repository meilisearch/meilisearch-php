<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Batch;
use Meilisearch\Contracts\BatchesResults;
use PHPUnit\Framework\TestCase;

final class BatchesResultsTest extends TestCase
{
    public function testToArrayMapsBatchesToRawArrays(): void
    {
        $firstRaw = [
            'uid' => 1,
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
        ];
        $secondRaw = [
            'uid' => 2,
            'details' => ['receivedDocuments' => 2],
            'stats' => [
                'totalNbTasks' => 2,
                'status' => ['succeeded' => 2],
                'types' => ['documentAdditionOrUpdate' => 2],
                'indexUids' => ['books' => 2],
            ],
            'duration' => null,
            'startedAt' => '2025-04-09T11:28:12.236789Z',
            'finishedAt' => null,
            'progress' => [
                'steps' => [
                    ['currentStep' => 'indexing', 'finished' => 1, 'total' => 2],
                ],
                'percentage' => 50.0,
            ],
        ];

        $firstBatch = Batch::fromArray($firstRaw);
        $secondBatch = Batch::fromArray($secondRaw);

        $results = new BatchesResults([
            'results' => [$firstBatch, $secondBatch],
            'from' => 1,
            'limit' => 2,
            'next' => 3,
            'total' => 5,
        ]);

        $array = $results->toArray();

        self::assertSame(1, $array['from']);
        self::assertSame(2, $array['limit']);
        self::assertSame(3, $array['next']);
        self::assertSame(5, $array['total']);
        self::assertSame(
            [$firstBatch->toArray(), $secondBatch->toArray()],
            $array['results'],
        );
    }
}
