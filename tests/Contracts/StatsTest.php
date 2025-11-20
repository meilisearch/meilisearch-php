<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\IndexStats;
use Meilisearch\Contracts\Stats;
use PHPUnit\Framework\TestCase;

final class StatsTest extends TestCase
{
    public function testConstruct(): void
    {
        $stats = new Stats(
            databaseSize: 1146880,
            usedDatabaseSize: 925696,
            lastUpdate: $date = new \DateTimeImmutable('2025-11-15 10:03:15.000000'),
            indexes: $indexes = [
                'stats_a902635d92481c0925e3a801bbc60c3e' => new IndexStats(
                    numberOfDocuments: 2,
                    rawDocumentDbSize: 4096,
                    avgDocumentSize: 2040,
                    isIndexing: false,
                    numberOfEmbeddings: 0,
                    numberOfEmbeddedDocuments: 0,
                    fieldDistribution: ['objectID' => 2, 'type' => 1],
                ),
            ],
        );

        self::assertSame(1146880, $stats->getDatabaseSize());
        self::assertSame(925696, $stats->getUsedDatabaseSize());
        self::assertSame($date, $stats->getLastUpdate());
        self::assertSame($indexes, $stats->getIndexes());
    }

    public function testFromArray(): void
    {
        $stats = Stats::fromArray([
            'databaseSize' => 1146880,
            'usedDatabaseSize' => 925696,
            'lastUpdate' => '2025-11-15T10:03:15.000000000Z',
            'indexes' => [
                'stats_a902635d92481c0925e3a801bbc60c3e' => [
                    'numberOfDocuments' => 2,
                    'rawDocumentDbSize' => 4096,
                    'avgDocumentSize' => 2040,
                    'isIndexing' => false,
                    'numberOfEmbeddings' => 0,
                    'numberOfEmbeddedDocuments' => 0,
                    'fieldDistribution' => ['objectID' => 2, 'type' => 1],
                ],
            ],
        ]);

        self::assertSame(1146880, $stats->getDatabaseSize());
        self::assertSame(925696, $stats->getUsedDatabaseSize());
        self::assertEquals(new \DateTimeImmutable('2025-11-15 10:03:15.000000'), $stats->getLastUpdate());
        self::assertEquals([
            'stats_a902635d92481c0925e3a801bbc60c3e' => new IndexStats(
                numberOfDocuments: 2,
                rawDocumentDbSize: 4096,
                avgDocumentSize: 2040,
                isIndexing: false,
                numberOfEmbeddings: 0,
                numberOfEmbeddedDocuments: 0,
                fieldDistribution: ['objectID' => 2, 'type' => 1],
            ),
        ], $stats->getIndexes());
    }
}
