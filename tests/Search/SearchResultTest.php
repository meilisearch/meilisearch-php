<?php

declare(strict_types=1);

namespace Tests\Search;

use MeiliSearch\Search\SearchResult;
use PHPUnit\Framework\TestCase;
use function strtoupper;

final class SearchResultTest extends TestCase
{
    public function testResultCanBeBuilt(): void
    {
        $result = new SearchResult([
            'hits' => [
                [
                    'title' => 'American Pie 2',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
                    'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
                    'release_date' => 997405200,
                ],
                [
                    'id' => '190859',
                    'title' => 'American Sniper',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
                    'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
                    'release_date' => 1418256000,
                ],
            ],
            'offset' => 0,
            'limit' => 20,
            'nbHits' => 976,
            'exhaustiveNbHits' => false,
            'processingTimeMs' => 35,
            'query' => 'american',
        ]);

        static::assertSame(2, $result->count());
        static::assertNotEmpty($result->getHits());

        static::assertEquals([
            'title' => 'American Pie 2',
            'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
            'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
            'release_date' => 997405200,
        ], $result->getHit(0));
        static::assertEquals([
            'id' => '190859',
            'title' => 'American Sniper',
            'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
            'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
            'release_date' => 1418256000,
        ], $result->getHit(1));
        static::assertNull($result->getHit(2));
        static::assertSame(0, $result->getOffset());
        static::assertSame(20, $result->getLimit());
        static::assertSame(2, $result->getNbHits());
        static::assertSame(976, $result->getHitsCount());
        static::assertFalse($result->getExhaustiveNbHits());
        static::assertSame(35, $result->getProcessingTimeMs());
        static::assertSame('american', $result->getQuery());
        static::assertNull($result->getExhaustiveFacetsCount());
        static::assertEmpty($result->getFacetsDistribution());
        static::assertSame(2, $result->getIterator()->count());

        static::assertArrayHasKey('hits', $result->toArray());
        static::assertArrayHasKey('offset', $result->toArray());
        static::assertArrayHasKey('limit', $result->toArray());
        static::assertArrayHasKey('nbHits', $result->toArray());
        static::assertArrayHasKey('exhaustiveNbHits', $result->toArray());
        static::assertArrayHasKey('processingTimeMs', $result->toArray());
        static::assertArrayHasKey('query', $result->toArray());
        static::assertArrayHasKey('exhaustiveFacetsCount', $result->toArray());
        static::assertArrayHasKey('facetsDistribution', $result->toArray());
    }

    public function testSearchResultCanBeFiltered(): void
    {
        $result = new SearchResult([
            'hits' => [
                [
                    'title' => 'American Pie 2',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
                    'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
                    'release_date' => 997405200,
                ],
                [
                    'id' => '190859',
                    'title' => 'American Sniper',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
                    'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
                    'release_date' => 1418256000,
                ],
            ],
            'offset' => 0,
            'limit' => 20,
            'nbHits' => 976,
            'exhaustiveNbHits' => false,
            'processingTimeMs' => 35,
            'query' => 'american',
        ]);

        $filteredResults = $result->filter(function (array $hit, int $_): bool {
            return 'AMERICAN SNIPER' === strtoupper($hit['title']);
        });

        static::assertSame(1, $filteredResults->count());
        static::assertNull($result->getHit(0));
        static::assertEquals([
            'id' => '190859',
            'title' => 'American Sniper',
            'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
            'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
            'release_date' => 1418256000,
        ], $result->getHit(1));
    }

    public function testResultCanBeReturnedAsJson(): void
    {
        $result = new SearchResult([
            'hits' => [
                [
                    'title' => 'American Pie 2',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
                    'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
                    'release_date' => 997405200,
                ],
                [
                    'id' => '190859',
                    'title' => 'American Sniper',
                    'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
                    'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
                    'release_date' => 1418256000,
                ],
            ],
            'offset' => 0,
            'limit' => 20,
            'nbHits' => 976,
            'exhaustiveNbHits' => false,
            'processingTimeMs' => 35,
            'query' => 'american',
        ]);

        $json = $result->toJson();

        static::assertStringContainsString('hits', $json);
        static::assertStringContainsString('offset', $json);
        static::assertStringContainsString('limit', $json);
        static::assertStringContainsString('hitsCount', $json);
        static::assertStringContainsString('nbHits', $json);
        static::assertStringContainsString('exhaustiveNbHits', $json);
        static::assertStringContainsString('processingTimeMs', $json);
        static::assertStringContainsString('query', $json);
        static::assertStringContainsString('exhaustiveFacetsCount', $json);
        static::assertStringContainsString('facetsDistribution', $json);
    }
}
