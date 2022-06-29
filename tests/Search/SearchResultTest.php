<?php

declare(strict_types=1);

namespace Tests\Search;

use MeiliSearch\Search\SearchResult;
use PHPUnit\Framework\TestCase;
use function strtoupper;

final class SearchResultTest extends TestCase
{
    private array $basicServerResponse = [];
    private SearchResult $resultWithFacets;
    private SearchResult $basicResult;

    protected function setUp(): void
    {
        parent::setUp();
        $this->basicServerResponse = [
            'hits' => [
                [
                    'id' => '1',
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
            'estimatedTotalHits' => 976,
            'processingTimeMs' => 35,
            'query' => 'american',
        ];
        $this->basicResult = new SearchResult($this->basicServerResponse);
        $serverResponseWithFacets = [
            'hits' => [
                [
                    'genre' => 'adventure',
                    'id' => 456,
                    'title' => 'Le Petit Prince',
                    'author' => 'Antoine de Saint-Exupéry',
                ],
                [
                    'genre' => 'fantasy',
                    'id' => 4,
                    'title' => 'Harry Potter and the Half-Blood Prince',
                    'author' => 'J. K. Rowling',
                ],
            ],
            'offset' => 0,
            'limit' => 20,
            'estimatedTotalHits' => 2,
            'processingTimeMs' => 1,
            'query' => 'prinec',
            'facetDistribution' => [
                'genre' => [
                    'fantasy' => 1,
                    'romance' => 0,
                    'adventure' => 1,
                ],
            ],
        ];
        $this->resultWithFacets = new SearchResult($serverResponseWithFacets);
    }

    public function testResultCanBeBuilt(): void
    {
        $this->assertCount(2, $this->basicResult);
        $this->assertNotEmpty($this->basicResult->getHits());

        $this->assertEquals([
            'id' => '1',
            'title' => 'American Pie 2',
            'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
            'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
            'release_date' => 997405200,
        ], $this->basicResult->getHit(0));
        $this->assertEquals([
            'id' => '190859',
            'title' => 'American Sniper',
            'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
            'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
            'release_date' => 1418256000,
        ], $this->basicResult->getHit(1));
        $this->assertNull($this->basicResult->getHit(2));
        $this->assertSame(0, $this->basicResult->getOffset());
        $this->assertSame(20, $this->basicResult->getLimit());
        $this->assertSame(2, $this->basicResult->getHitsCount());
        $this->assertSame(976, $this->basicResult->getEstimatedTotalHits());
        $this->assertSame(35, $this->basicResult->getProcessingTimeMs());
        $this->assertSame('american', $this->basicResult->getQuery());
        $this->assertEmpty($this->basicResult->getFacetDistribution());
        $this->assertCount(2, $this->basicResult);

        $this->assertArrayHasKey('hits', $this->basicResult->toArray());
        $this->assertArrayHasKey('offset', $this->basicResult->toArray());
        $this->assertArrayHasKey('limit', $this->basicResult->toArray());
        $this->assertArrayHasKey('estimatedTotalHits', $this->basicResult->toArray());
        $this->assertArrayHasKey('hitsCount', $this->basicResult->toArray());
        $this->assertArrayHasKey('processingTimeMs', $this->basicResult->toArray());
        $this->assertArrayHasKey('query', $this->basicResult->toArray());
        $this->assertArrayHasKey('facetDistribution', $this->basicResult->toArray());
    }

    public function testSearchResultCanBeFiltered(): void
    {
        $keepAmericanSniperFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'American Sniper' === $hit['title']; }
            );
        };

        $options = ['transformHits' => $keepAmericanSniperFunc];

        $filteredResults = $this->basicResult->applyOptions($options);

        $this->assertCount(1, $filteredResults);
        $this->assertSame(1, $filteredResults->getHitsCount());
        $this->assertSame(976, $filteredResults->getEstimatedTotalHits());
        $this->assertEquals([
            'id' => '190859',
            'title' => 'American Sniper',
            'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
            'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
            'release_date' => 1418256000,
        ], $filteredResults->getHit(1)); // Not getHits(0) because array_filter() does not reorder the indexes after filtering.
    }

    public function testResultCanBeReturnedAsJson(): void
    {
        $this->assertJsonStringEqualsJsonString(
            '{"hits":[{"id":"1","title":"American Pie 2","poster":"https:\/\/image.tmdb.org\/t\/p\/w1280\/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg","overview":"The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...","release_date":997405200},{"id":"190859","title":"American Sniper","poster":"https:\/\/image.tmdb.org\/t\/p\/w1280\/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg","overview":"U.S. Navy SEAL Chris Kyle takes his sole mission\u2014protect his comrades\u2014to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...","release_date":1418256000}],"offset":0,"limit":20,"estimatedTotalHits":976,"hitsCount":2,"processingTimeMs":35,"query":"american","facetDistribution":[]}',
            $this->basicResult->toJSON()
        );
    }

    public function testResultCanBeReturnedAsAnIterator(): void
    {
        $iterator = $this->basicResult->getIterator();

        /* @phpstan-ignore-next-line */
        $this->assertIsIterable($this->basicResult);
        $this->assertCount(2, $iterator);

        $this->assertEquals([
            'id' => '1',
            'title' => 'American Pie 2',
            'poster' => 'https://image.tmdb.org/t/p/w1280/q4LNgUnRfltxzp3gf1MAGiK5LhV.jpg',
            'overview' => 'The whole gang are back and as close as ever. They decide to get even closer by spending the summer together at a beach house. They decide to hold the biggest...',
            'release_date' => 997405200,
        ], $iterator->current());

        $iterator->next();

        $this->assertEquals([
            'id' => '190859',
            'title' => 'American Sniper',
            'poster' => 'https://image.tmdb.org/t/p/w1280/svPHnYE7N5NAGO49dBmRhq0vDQ3.jpg',
            'overview' => 'U.S. Navy SEAL Chris Kyle takes his sole mission—protect his comrades—to heart and becomes one of the most lethal snipers in American history. His pinpoint accuracy not only saves countless lives but also makes him a prime...',
            'release_date' => 1418256000,
        ], $iterator->current());
    }

    public function testGetRaw(): void
    {
        $this->assertEquals($this->basicServerResponse, $this->basicResult->getRaw());
    }

    public function testTransformHitsMethod(): void
    {
        $keepAmericanSniperFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'American Sniper' === $hit['title']; }
            );
        };

        $response = $this->basicResult->transformHits($keepAmericanSniperFunc);

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame('American Sniper', $response->getHit(1)['title']); // Not getHits(0) because array_filter() does not reorder the indexes after filtering.
        $this->assertSame(976, $response->getEstimatedTotalHits());
        $this->assertSame(1, $response->getHitsCount());
        $this->assertCount(1, $response);
    }

    public function testTransformFacetsDistributionMethod(): void
    {
        $facetsToUpperFunc = function (array $facets): array {
            $changeOneFacet = function (array $facet): array {
                $result = [];
                foreach ($facet as $k => $v) {
                    $result[strtoupper($k)] = $v;
                }

                return $result;
            };

            return array_map($changeOneFacet, $facets);
        };

        $response = $this->resultWithFacets->transformFacetDistribution($facetsToUpperFunc);

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
        $this->assertCount(3, $response->getFacetDistribution()['genre']);
        $this->assertEquals(0, $response->getFacetDistribution()['genre']['ROMANCE']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['FANTASY']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['ADVENTURE']);
    }
}
