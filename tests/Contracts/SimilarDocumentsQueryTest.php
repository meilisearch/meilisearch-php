<?php

declare(strict_types=1);

namespace Contracts;

use Meilisearch\Contracts\SimilarDocumentsQuery;
use PHPUnit\Framework\TestCase;

final class SimilarDocumentsQueryTest extends TestCase
{
    /**
     * @param int|string $id
     *
     * @testWith [123]
     *           ["test"]
     */
    public function testConstruct($id): void
    {
        $data = new SimilarDocumentsQuery($id);

        self::assertSame(['id' => $id], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setOffset(66);

        self::assertSame(['id' => 'test', 'offset' => 66], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setLimit(50);

        self::assertSame(['id' => 'test', 'limit' => 50], $data->toArray());
    }

    public function testSetFilter(): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setFilter([
            ['genres = horror', 'genres = mystery'],
            "director = 'Jordan Peele'",
        ]);

        self::assertSame(['id' => 'test', 'filter' => [['genres = horror', 'genres = mystery'], "director = 'Jordan Peele'"]], $data->toArray());
    }

    public function testSetEmbedder(): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setEmbedder('default');

        self::assertSame(['id' => 'test', 'embedder' => 'default'], $data->toArray());
    }

    public function testSetAttributesToRetrieve(): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setAttributesToRetrieve(['name', 'price']);

        self::assertSame(['id' => 'test', 'attributesToRetrieve' => ['name', 'price']], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetShowRankingScore(bool $showRankingScore): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setShowRankingScore($showRankingScore);

        self::assertSame(['id' => 'test', 'showRankingScore' => $showRankingScore], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetShowRankingScoreDetails(bool $showRankingScoreDetails): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setShowRankingScoreDetails($showRankingScoreDetails);

        self::assertSame(['id' => 'test', 'showRankingScoreDetails' => $showRankingScoreDetails], $data->toArray());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetRetrieveVectors(bool $retrieveVectors): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setRetrieveVectors($retrieveVectors);

        self::assertSame(['id' => 'test', 'retrieveVectors' => $retrieveVectors], $data->toArray());
    }

    /**
     * @testWith [123]
     *           [0.123]
     *
     * @param int|float $rankingScoreThreshold
     */
    public function testSetRankingScoreThreshold($rankingScoreThreshold): void
    {
        $data = (new SimilarDocumentsQuery('test'))->setRankingScoreThreshold($rankingScoreThreshold);

        self::assertSame(['id' => 'test', 'rankingScoreThreshold' => $rankingScoreThreshold], $data->toArray());
    }
}
