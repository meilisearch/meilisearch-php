<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\FederationOptions;
use Meilisearch\Contracts\HybridSearchOptions;
use Meilisearch\Contracts\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new SearchQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetIndexUid(): void
    {
        $data = (new SearchQuery())->setIndexUid('movies');

        self::assertSame(['indexUid' => 'movies'], $data->toArray());
    }

    public function testSetQuery(): void
    {
        $data = (new SearchQuery())->setQuery('shifu');

        self::assertSame(['q' => 'shifu'], $data->toArray());
    }

    public function testSetFilter(): void
    {
        $data = (new SearchQuery())->setFilter(['rating > 3']);

        self::assertSame(['filter' => ['rating > 3']], $data->toArray());
    }

    public function testSetLocales(): void
    {
        $data = (new SearchQuery())->setLocales(['en', 'fr']);

        self::assertSame(['locales' => ['en', 'fr']], $data->toArray());
    }

    public function testSetAttributesToRetrieve(): void
    {
        $data = (new SearchQuery())->setAttributesToRetrieve(['overview', 'title']);

        self::assertSame(['attributesToRetrieve' => ['overview', 'title']], $data->toArray());
    }

    public function testSetAttributesToCrop(): void
    {
        $data = (new SearchQuery())->setAttributesToCrop(['attributeNameA:5', 'attributeNameB:9']);

        self::assertSame(['attributesToCrop' => ['attributeNameA:5', 'attributeNameB:9']], $data->toArray());
    }

    public function testSetCropLength(): void
    {
        $data = (new SearchQuery())->setCropLength(10);

        self::assertSame(['cropLength' => 10], $data->toArray());
    }

    public function testSetAttributesToHighlight(): void
    {
        $data = (new SearchQuery())->setAttributesToHighlight(['overview', 'title']);

        self::assertSame(['attributesToHighlight' => ['overview', 'title']], $data->toArray());
    }

    /**
     * @testWith [""]
     *           ["[…]"]
     */
    public function testSetCropMarker(string $marker): void
    {
        $data = (new SearchQuery())->setCropMarker($marker);

        self::assertSame(['cropMarker' => $marker], $data->toArray());
    }

    /**
     * @testWith [""]
     *           ["*"]
     *           ["__"]
     *           ["<em>"]
     *           ["<strong>"]
     */
    public function testSetHighlightPreTag(string $tag): void
    {
        $data = (new SearchQuery())->setHighlightPreTag($tag);

        self::assertSame(['highlightPreTag' => $tag], $data->toArray());
    }

    /**
     * @testWith [""]
     *           ["*"]
     *           ["__"]
     *           ["</em>"]
     *           ["</strong>"]
     */
    public function testSetHighlightPostTag(string $tag): void
    {
        $data = (new SearchQuery())->setHighlightPostTag($tag);

        self::assertSame(['highlightPostTag' => $tag], $data->toArray());
    }

    public function testSetFacets(): void
    {
        $data = (new SearchQuery())->setFacets(['attributeA', 'attributeB']);

        self::assertSame(['facets' => ['attributeA', 'attributeB']], $data->toArray());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testSetShowMatchesPosition(?bool $value): void
    {
        $data = (new SearchQuery())->setShowMatchesPosition($value);

        self::assertSame(['showMatchesPosition' => $value], $data->toArray());
    }

    public function testSetSort(): void
    {
        $data = (new SearchQuery())->setSort(['price:asc']);

        self::assertSame(['sort' => ['price:asc']], $data->toArray());
    }

    /**
     * @param 'last'|'all'|'frequency' $strategy
     *
     * @testWith ["last"]
     *           ["all"]
     *           ["frequency"]
     */
    public function testSetMatchingStrategy(string $strategy): void
    {
        $data = (new SearchQuery())->setMatchingStrategy($strategy);

        self::assertSame(['matchingStrategy' => $strategy], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new SearchQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new SearchQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }

    public function testSetHitsPerPage(): void
    {
        $data = (new SearchQuery())->setHitsPerPage(0);

        self::assertSame(['hitsPerPage' => 0], $data->toArray());
    }

    public function testSetPage(): void
    {
        $data = (new SearchQuery())->setPage(0);

        self::assertSame(['page' => 0], $data->toArray());
    }

    public function testSetHybrid(): void
    {
        $data = (new SearchQuery())->setHybrid((new HybridSearchOptions())->setSemanticRatio(0.5));

        self::assertSame(['hybrid' => ['semanticRatio' => 0.5]], $data->toArray());
    }

    public function testSetAttributesToSearchOn(): void
    {
        $data = (new SearchQuery())->setAttributesToSearchOn(['overview']);

        self::assertSame(['attributesToSearchOn' => ['overview']], $data->toArray());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testSetShowRankingScore(?bool $value): void
    {
        $data = (new SearchQuery())->setShowRankingScore($value);

        self::assertSame(['showRankingScore' => $value], $data->toArray());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testSetShowRankingScoreDetails(?bool $value): void
    {
        $data = (new SearchQuery())->setShowRankingScoreDetails($value);

        self::assertSame(['showRankingScoreDetails' => $value], $data->toArray());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testSetShowPerformanceDetails(?bool $value): void
    {
        $data = (new SearchQuery())->setShowPerformanceDetails($value);

        self::assertSame(['showPerformanceDetails' => $value], $data->toArray());
    }

    public function testSetRankingScoreThreshold(): void
    {
        $data = (new SearchQuery())->setRankingScoreThreshold(0.123);

        self::assertSame(['rankingScoreThreshold' => 0.123], $data->toArray());
    }

    public function testSetDistinct(): void
    {
        $data = (new SearchQuery())->setDistinct('genre');

        self::assertSame(['distinct' => 'genre'], $data->toArray());
    }

    public function testSetFederationOptions(): void
    {
        $data = (new SearchQuery())->setFederationOptions((new FederationOptions())->setWeight(0.5));

        self::assertSame(['federationOptions' => ['weight' => 0.5]], $data->toArray());
    }
}
