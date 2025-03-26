<?php

declare(strict_types=1);

namespace Contracts;

use Meilisearch\Contracts\FacetSearchQuery;
use PHPUnit\Framework\TestCase;

final class FacetSearchQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new FacetSearchQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetFacetName(): void
    {
        $data = (new FacetSearchQuery())->setFacetName('genres');

        self::assertSame(['facetName' => 'genres'], $data->toArray());
    }

    public function testSetFacetQuery(): void
    {
        $data = (new FacetSearchQuery())->setFacetQuery('fiction');

        self::assertSame(['facetQuery' => 'fiction'], $data->toArray());
    }

    public function testSetQ(): void
    {
        $data = (new FacetSearchQuery())->setQuery('a=b');

        self::assertSame(['q' => 'a=b'], $data->toArray());
    }

    public function testSetFilter(): void
    {
        $data = (new FacetSearchQuery())->setFilter(['rating > 3']);

        self::assertSame(['filter' => ['rating > 3']], $data->toArray());
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
        $data = (new FacetSearchQuery())->setMatchingStrategy($strategy);

        self::assertSame(['matchingStrategy' => $strategy], $data->toArray());
    }

    public function testSetAttributesToSearchOn(): void
    {
        $data = (new FacetSearchQuery())->setAttributesToSearchOn(['overview']);

        self::assertSame(['attributesToSearchOn' => ['overview']], $data->toArray());
    }

    public function testSetExhaustiveFacetsCount(): void
    {
        $data = (new FacetSearchQuery())->setExhaustiveFacetsCount(true);

        self::assertSame(['exhaustiveFacetsCount' => true], $data->toArray());
    }
}
