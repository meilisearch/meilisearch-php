<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\BatchEmbedderRequests;
use Meilisearch\Contracts\BatchStats;
use PHPUnit\Framework\TestCase;

final class BatchStatsTest extends TestCase
{
    public function testConstruct(): void
    {
        $embedderRequests = new BatchEmbedderRequests(
            total: 10,
            failed: 2,
            lastError: 'timeout',
        );

        $stats = new BatchStats(
            totalNbTasks: 1,
            status: ['succeeded' => 1],
            types: ['documentAdditionOrUpdate' => 1],
            indexUids: ['movies' => 1],
            progressTrace: ['indexingDocuments' => '100%'],
            writeChannelCongestion: ['attempts' => 0],
            internalDatabaseSizes: ['documents' => '4.0 KiB'],
            embedderRequests: $embedderRequests,
        );

        self::assertSame(1, $stats->getTotalNbTasks());
        self::assertSame(['succeeded' => 1], $stats->getStatus());
        self::assertSame(['documentAdditionOrUpdate' => 1], $stats->getTypes());
        self::assertSame(['movies' => 1], $stats->getIndexUids());
        self::assertSame(['indexingDocuments' => '100%'], $stats->getProgressTrace());
        self::assertSame(['attempts' => 0], $stats->getWriteChannelCongestion());
        self::assertSame(['documents' => '4.0 KiB'], $stats->getInternalDatabaseSizes());
        self::assertSame($embedderRequests, $stats->getEmbedderRequests());
    }

    public function testConstructWithOptionalFieldsNull(): void
    {
        $stats = new BatchStats(
            totalNbTasks: 2,
            status: ['succeeded' => 2],
            types: ['documentAdditionOrUpdate' => 2],
            indexUids: ['books' => 2],
        );

        self::assertSame(2, $stats->getTotalNbTasks());
        self::assertSame(['succeeded' => 2], $stats->getStatus());
        self::assertSame(['documentAdditionOrUpdate' => 2], $stats->getTypes());
        self::assertSame(['books' => 2], $stats->getIndexUids());
        self::assertNull($stats->getProgressTrace());
        self::assertNull($stats->getWriteChannelCongestion());
        self::assertNull($stats->getInternalDatabaseSizes());
        self::assertNull($stats->getEmbedderRequests());
    }

    public function testFromArray(): void
    {
        $stats = BatchStats::fromArray([
            'totalNbTasks' => 1,
            'status' => ['succeeded' => 1],
            'types' => ['documentAdditionOrUpdate' => 1],
            'indexUids' => ['movies' => 1],
            'progressTrace' => ['indexingDocuments' => '100%'],
            'writeChannelCongestion' => ['attempts' => 0],
            'internalDatabaseSizes' => ['documents' => '4.0 KiB'],
            'embedderRequests' => [
                'total' => 10,
                'failed' => 2,
                'lastError' => 'timeout',
            ],
        ]);

        self::assertSame(1, $stats->getTotalNbTasks());
        self::assertSame(['succeeded' => 1], $stats->getStatus());
        self::assertSame(['documentAdditionOrUpdate' => 1], $stats->getTypes());
        self::assertSame(['movies' => 1], $stats->getIndexUids());
        self::assertSame(['indexingDocuments' => '100%'], $stats->getProgressTrace());
        self::assertSame(['attempts' => 0], $stats->getWriteChannelCongestion());
        self::assertSame(['documents' => '4.0 KiB'], $stats->getInternalDatabaseSizes());
        self::assertEquals(new BatchEmbedderRequests(
            total: 10,
            failed: 2,
            lastError: 'timeout',
        ), $stats->getEmbedderRequests());
    }

    public function testFromArrayWithOptionalFieldsAbsent(): void
    {
        $stats = BatchStats::fromArray([
            'totalNbTasks' => 1,
            'status' => ['succeeded' => 1],
            'types' => ['documentAdditionOrUpdate' => 1],
            'indexUids' => ['movies' => 1],
        ]);

        self::assertSame(1, $stats->getTotalNbTasks());
        self::assertNull($stats->getProgressTrace());
        self::assertNull($stats->getWriteChannelCongestion());
        self::assertNull($stats->getInternalDatabaseSizes());
        self::assertNull($stats->getEmbedderRequests());
    }

    public function testEmbedderRequestsConstruct(): void
    {
        $requests = new BatchEmbedderRequests(
            total: 5,
            failed: 1,
            lastError: 'rate limited',
        );

        self::assertSame(5, $requests->getTotal());
        self::assertSame(1, $requests->getFailed());
        self::assertSame('rate limited', $requests->getLastError());
    }

    public function testEmbedderRequestsConstructWithOptionalLastErrorNull(): void
    {
        $requests = new BatchEmbedderRequests(
            total: 3,
            failed: 0,
        );

        self::assertSame(3, $requests->getTotal());
        self::assertSame(0, $requests->getFailed());
        self::assertNull($requests->getLastError());
    }

    public function testEmbedderRequestsFromArray(): void
    {
        $requests = BatchEmbedderRequests::fromArray([
            'total' => 10,
            'failed' => 2,
            'lastError' => 'timeout',
        ]);

        self::assertSame(10, $requests->getTotal());
        self::assertSame(2, $requests->getFailed());
        self::assertSame('timeout', $requests->getLastError());
    }

    public function testEmbedderRequestsFromArrayWithOptionalLastErrorAbsent(): void
    {
        $requests = BatchEmbedderRequests::fromArray([
            'total' => 4,
            'failed' => 0,
        ]);

        self::assertSame(4, $requests->getTotal());
        self::assertSame(0, $requests->getFailed());
        self::assertNull($requests->getLastError());
    }
}
