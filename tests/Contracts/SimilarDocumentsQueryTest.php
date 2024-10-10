<?php

declare(strict_types=1);

namespace Contracts;

use Meilisearch\Contracts\SimilarDocumentsQuery;
use PHPUnit\Framework\TestCase;

final class SimilarDocumentsQueryTest extends TestCase
{
    /**
     * @param int|string       $id
     * @param non-empty-string $embedder
     *
     * @testWith [123, "default"]
     *           ["test", "manual"]
     */
    public function testConstruct($id, string $embedder): void
    {
        $data = new SimilarDocumentsQuery($id, $embedder);

        self::assertSame(['id' => $id, 'embedder' => $embedder], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setOffset(66);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'offset' => 66], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setLimit(50);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'limit' => 50], $data->toArray());
    }

    public function testSetFilter(): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setFilter([
            ['genres = horror', 'genres = mystery'],
            "director = 'Jordan Peele'",
        ]);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'filter' => [['genres = horror', 'genres = mystery'], "director = 'Jordan Peele'"]], $data->toArray());
    }

    public function testSetAttributesToRetrieve(): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setAttributesToRetrieve(['name', 'price']);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'attributesToRetrieve' => ['name', 'price']], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetShowRankingScore(bool $showRankingScore): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setShowRankingScore($showRankingScore);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'showRankingScore' => $showRankingScore], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetShowRankingScoreDetails(bool $showRankingScoreDetails): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setShowRankingScoreDetails($showRankingScoreDetails);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'showRankingScoreDetails' => $showRankingScoreDetails], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetRetrieveVectors(bool $retrieveVectors): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setRetrieveVectors($retrieveVectors);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'retrieveVectors' => $retrieveVectors], $data->toArray());
    }

    /**
     * @testWith [123]
     *           [0.123]
     *
     * @param int|float $rankingScoreThreshold
     */
    public function testSetRankingScoreThreshold($rankingScoreThreshold): void
    {
        $data = (new SimilarDocumentsQuery('test', 'default'))->setRankingScoreThreshold($rankingScoreThreshold);

        self::assertSame(['id' => 'test', 'embedder' => 'default', 'rankingScoreThreshold' => $rankingScoreThreshold], $data->toArray());
    }
}
