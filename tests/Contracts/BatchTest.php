<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Batch;
use Meilisearch\Contracts\BatchProgress;
use Meilisearch\Contracts\BatchProgressStep;
use Meilisearch\Contracts\BatchStats;
use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
use Meilisearch\Exceptions\LogicException;
use PHPUnit\Framework\TestCase;

final class BatchTest extends TestCase
{
    public function testCreate(): void
    {
        $details = new UnknownTaskDetails(['receivedDocuments' => 1]);
        $stats = $this->minimalStats();
        $raw = [
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
            'batchStrategy' => 'sizeLimit',
        ];

        $batch = new Batch(
            uid: 1,
            details: $details,
            stats: $stats,
            duration: 'PT1S',
            startedAt: new \DateTimeImmutable('2025-04-09T10:28:12.236789Z'),
            finishedAt: new \DateTimeImmutable('2025-04-09T10:28:13.236789Z'),
            progress: null,
            batchStrategy: 'sizeLimit',
            raw: $raw,
        );

        self::assertSame(1, $batch->getUid());
        self::assertSame($details, $batch->getDetails());
        self::assertSame($stats, $batch->getStats());
        self::assertSame('PT1S', $batch->getDuration());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.236789Z'), $batch->getStartedAt());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:13.236789Z'), $batch->getFinishedAt());
        self::assertNull($batch->getProgress());
        self::assertSame('sizeLimit', $batch->getBatchStrategy());
        self::assertTrue(isset($batch['uid']));
        self::assertSame(1, $batch['uid']);
        self::assertSame($raw, $batch->toArray());
    }

    public function testCreateProcessingBatch(): void
    {
        $progress = new BatchProgress(
            steps: [
                new BatchProgressStep(
                    currentStep: 'indexing',
                    finished: 1,
                    total: 2,
                ),
            ],
            percentage: 50.0,
        );

        $raw = [
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

        $batch = new Batch(
            uid: 2,
            details: new UnknownTaskDetails(['receivedDocuments' => 2]),
            stats: $this->minimalStats(totalNbTasks: 2, indexUid: 'books'),
            duration: null,
            startedAt: new \DateTimeImmutable('2025-04-09T11:28:12.236789Z'),
            finishedAt: null,
            progress: $progress,
            batchStrategy: null,
            raw: $raw,
        );

        self::assertSame(2, $batch->getUid());
        self::assertNull($batch->getDuration());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T11:28:12.236789Z'), $batch->getStartedAt());
        self::assertNull($batch->getFinishedAt());
        self::assertSame($progress, $batch->getProgress());
        self::assertNull($batch->getBatchStrategy());
    }

    public function testOffsetSetThrows(): void
    {
        $batch = Batch::fromArray($this->finishedRaw());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The Batch object is immutable.');

        $batch['uid'] = 2;
    }

    public function testOffsetUnsetThrows(): void
    {
        $batch = Batch::fromArray($this->finishedRaw());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The Batch object is immutable.');

        unset($batch['uid']);
    }

    public function testFromArrayFinished(): void
    {
        $raw = $this->finishedRaw();
        $batch = Batch::fromArray($raw);

        self::assertSame(1, $batch->getUid());
        self::assertEquals(new UnknownTaskDetails(['receivedDocuments' => 1]), $batch->getDetails());
        self::assertSame(1, $batch->getStats()->getTotalNbTasks());
        self::assertSame('PT1S', $batch->getDuration());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:12.236789Z'), $batch->getStartedAt());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T10:28:13.236789Z'), $batch->getFinishedAt());
        self::assertNull($batch->getProgress());
        self::assertNull($batch->getBatchStrategy());
        self::assertSame($raw, $batch->toArray());
    }

    public function testFromArrayProcessing(): void
    {
        $raw = [
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

        $batch = Batch::fromArray($raw);

        self::assertSame(2, $batch->getUid());
        self::assertNull($batch->getDuration());
        self::assertEquals(new \DateTimeImmutable('2025-04-09T11:28:12.236789Z'), $batch->getStartedAt());
        self::assertNull($batch->getFinishedAt());
        self::assertEquals(new BatchProgress(
            steps: [
                new BatchProgressStep(
                    currentStep: 'indexing',
                    finished: 1,
                    total: 2,
                ),
            ],
            percentage: 50.0,
        ), $batch->getProgress());
        self::assertNull($batch->getBatchStrategy());
        self::assertSame($raw, $batch->toArray());
    }

    public function testFromArrayWithBatchStrategy(): void
    {
        $raw = $this->finishedRaw();
        $raw['batchStrategy'] = 'sizeLimit';

        $batch = Batch::fromArray($raw);

        self::assertSame('sizeLimit', $batch->getBatchStrategy());
        self::assertSame($raw, $batch->toArray());
    }

    /**
     * @return array{
     *     uid: non-negative-int,
     *     details: array<mixed>,
     *     stats: array{
     *         totalNbTasks: non-negative-int,
     *         status: array<non-empty-string, non-negative-int>,
     *         types: array<non-empty-string, non-negative-int>,
     *         indexUids: array<non-empty-string, non-negative-int>
     *     },
     *     duration: non-empty-string,
     *     startedAt: non-empty-string,
     *     finishedAt: non-empty-string,
     *     progress: null
     * }
     */
    private function finishedRaw(): array
    {
        return [
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
    }

    private function minimalStats(int $totalNbTasks = 1, string $indexUid = 'movies'): BatchStats
    {
        return new BatchStats(
            totalNbTasks: $totalNbTasks,
            status: ['succeeded' => $totalNbTasks],
            types: ['documentAdditionOrUpdate' => $totalNbTasks],
            indexUids: [$indexUid => $totalNbTasks],
        );
    }
}
