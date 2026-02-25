<?php

declare(strict_types=1);

namespace Endpoints;

use Meilisearch\Contracts\HybridSearchOptions;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Http\Client;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class SearchQueryTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::DOCUMENTS)->wait();
    }

    public function testBasicSearchWithFinitePagination(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setHitsPerPage(2);
        $response = $this->index->search('prince', $searchQuery);

        $this->assertFinitePagination($response->toArray());
        self::assertCount(2, $response->getHits());

        self::assertSame(2, $response->getHitsPerPage());
        self::assertSame(1, $response->getPage());
        self::assertSame(1, $response->getTotalPages());
        self::assertSame(2, $response->getTotalHits());

        self::assertNull($response->getEstimatedTotalHits());
        self::assertNull($response->getOffset());
        self::assertNull($response->getLimit());

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);

        $this->assertFinitePagination($response);
    }

    public function testSearchWithOptions(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(1);
        $response = $this->index->search('prince', $searchQuery);

        self::assertCount(1, $response->getHits());

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);

        self::assertCount(1, $response['hits']);
    }

    public function testParametersCropMarker(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(1);
        $searchQuery->setAttributesToCrop(['title']);
        $searchQuery->setCropLength(2);
        $response = $this->index->search('blood', $searchQuery);
        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('…Half-Blood…', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('blood', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('…Half-Blood…', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedCropMarker(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(1);
        $searchQuery->setAttributesToCrop(['title']);
        $searchQuery->setCropLength(3);
        $searchQuery->setCropMarker('(ꈍᴗꈍ)');
        $response = $this->index->search('blood', $searchQuery);

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('blood', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response['hits'][0]['_formatted']['title']);
    }

    public function testSearchWithMatchingStrategyALL(): void
    {
        $this->index->updateSearchableAttributes(['comment'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setMatchingStrategy('all');
        $response = $this->index->search('another french book', $searchQuery);

        self::assertCount(1, $response->getHits());
    }

    public function testSearchWithMatchingStrategyLAST(): void
    {
        $this->index->updateSearchableAttributes(['comment'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setMatchingStrategy('last');
        $response = $this->index->search('french book', $searchQuery);

        self::assertCount(2, $response->getHits());
    }

    public function testParametersWithHighlightTag(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(1);
        $searchQuery->setAttributesToHighlight(['*']);
        $response = $this->index->search('and', $searchQuery);

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('Pride <em>and</em> Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('and', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('Pride <em>and</em> Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedHighlightTag(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(1);
        $searchQuery->setAttributesToHighlight(['*']);
        $searchQuery->setHighlightPreTag('(⊃｡•́‿•̀｡)⊃ ');
        $searchQuery->setHighlightPostTag(' ⊂(´• ω •`⊂)');
        $response = $this->index->search('and', $searchQuery);

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('and', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersArray(): void
    {
        $this->index->updateFilterableAttributes(['title'])->wait();
        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(5);
        $searchQuery->setOffset(0);
        $searchQuery->setAttributesToRetrieve(['id', 'title']);
        $searchQuery->setAttributesToCrop(['id', 'title']);
        $searchQuery->setCropLength(6);
        $searchQuery->setAttributesToHighlight(['title']);
        $searchQuery->setFilter(['title = "Le Petit Prince"']);
        $searchQuery->setShowMatchesPosition(true);
        $response = $this->index->search('prince', $searchQuery);

        self::assertArrayHasKey('_matchesPosition', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_matchesPosition', $response['hits'][0]);
        self::assertArrayHasKey('title', $response['hits'][0]['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertArrayNotHasKey('comment', $response['hits'][0]);
        self::assertArrayNotHasKey('comment', $response['hits'][0]['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersCanBeAStar(): void
    {
        $this->index->updateFilterableAttributes(['title'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setLimit(5);
        $searchQuery->setOffset(0);
        $searchQuery->setAttributesToRetrieve(['*']);
        $searchQuery->setAttributesToCrop(['*']);
        $searchQuery->setCropLength(6);
        $searchQuery->setAttributesToHighlight(['*']);
        $searchQuery->setFilter(['title = "Le Petit Prince"']);
        $response = $this->index->search('prince', $searchQuery);

        self::assertArrayHasKey('_matchesPosition', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertArrayHasKey('comment', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_matchesPosition', $response['hits'][0]);
        self::assertArrayHasKey('title', $response['hits'][0]['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertArrayHasKey('comment', $response['hits'][0]);
        self::assertArrayNotHasKey('comment', $response['hits'][0]['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testSearchWithFilterCanBeInt(): void
    {
        $this->index->updateFilterableAttributes(['id', 'genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFilter(['id < 12']);
        $response = $this->index->search('prince', $searchQuery);

        self::assertSame(1, $response->getEstimatedTotalHits());
        self::assertCount(1, $response->getHits());
        self::assertSame(4, $response->getHit(0)['id']);

        $searchQuery = new SearchQuery();
        $searchQuery->setFilter(['genre = fantasy AND id < 12']);
        $response = $this->index->search('', $searchQuery);

        self::assertSame(2, $response->getEstimatedTotalHits());
        self::assertCount(2, $response->getHits());
        self::assertSame(1, $response->getHit(0)['id']);
        self::assertSame(4, $response->getHit(1)['id']);
    }

    public function testBasicSearchWithFacetDistribution(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre']);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayHasKey('facetDistribution', $response->toArray());
        self::assertArrayHasKey('genre', $response->getFacetDistribution());
        self::assertSame($response->getFacetDistribution()['genre']['fantasy'], 1);
        self::assertSame($response->getFacetDistribution()['genre']['adventure'], 1);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertArrayHasKey('facetDistribution', $response);
        self::assertArrayHasKey('genre', $response['facetDistribution']);
        self::assertSame($response['facetDistribution']['genre']['fantasy'], 1);
        self::assertSame($response['facetDistribution']['genre']['adventure'], 1);
    }

    public function testBasicSearchWithFilters(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFilter(['genre = fantasy']);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testBasicSearchWithMultipleFilter(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFilter(['genre = fantasy', ['genre = fantasy', 'genre = fantasy']]);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testCustomSearchWithFilterAndAttributesToRetrieve(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFilter(['genre = fantasy']);
        $searchQuery->setAttributesToRetrieve(['id', 'title']);
        $response = $this->index->search('prince', $searchQuery);

        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);
        self::assertArrayHasKey('id', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0));

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
        self::assertArrayHasKey('id', $response['hits'][0]);
        self::assertArrayHasKey('title', $response['hits'][0]);
        self::assertArrayNotHasKey('comment', $response['hits'][0]);
    }

    public function testSearchSortWithString(): void
    {
        $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ])->wait();
        $this->index->updateSortableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setSort(['genre:asc']);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(456, $response->getHit(0)['id']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(456, $response['hits'][0]['id']);
    }

    public function testSearchSortWithInt(): void
    {
        $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ])->wait();
        $this->index->updateSortableAttributes(['id'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setSort(['id:asc']);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchSortWithMultipleParameter(): void
    {
        $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ])->wait();
        $this->index->updateSortableAttributes(['id', 'title'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setSort(['id:asc', 'title:asc']);
        $response = $this->index->search('prince', $searchQuery);
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', $searchQuery, [
            'raw' => true,
        ]);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testBasicSearchWithFacetsOption(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre']);
        $response = $this->index->search(
            'prince',
            $searchQuery
        );

        self::assertCount(2, $response->getFacetDistribution()['genre']);
        self::assertSame(1, $response->getFacetDistribution()['genre']['adventure']);
        self::assertSame(1, $response->getFacetDistribution()['genre']['fantasy']);
        self::assertCount(2, $response->getRaw()['facetDistribution']['genre']);
        self::assertSame($response->getRaw()['hits'], $response->getHits());
        self::assertSame($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
    }

    public function testBasicSearchWithFacetsOptionAndMultipleFacets(): void
    {
        $this->index->addDocuments([['id' => 32, 'title' => 'The Witcher', 'genre' => 'adventure', 'adaptation' => 'video game']])->wait();
        $this->index->updateFilterableAttributes(['genre', 'adaptation'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre', 'adaptation']);
        $response = $this->index->search(
            'witch',
            $searchQuery
        );

        self::assertCount(1, $response->getFacetDistribution()['genre']);
        self::assertSame(1, $response->getFacetDistribution()['genre']['adventure']);
        self::assertCount(1, $response->getFacetDistribution()['adaptation']);
        self::assertSame(1, $response->getFacetDistribution()['adaptation']['video game']);
        self::assertCount(1, $response->getRaw()['facetDistribution']['adaptation']);
        self::assertCount(1, $response->getRaw()['facetDistribution']['genre']);
        self::assertSame($response->getRaw()['hits'], $response->getHits());
        self::assertSame($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
    }

    public function testVectorSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateEmbedders(['manual' => ['source' => 'userProvided', 'dimensions' => 3]])->wait();
        $index->updateDocuments(self::VECTOR_MOVIES)->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setVector([0.5, 0.3, 0.85]);
        $searchQuery->setHybrid((new HybridSearchOptions())->setSemanticRatio(1.0)->setEmbedder('manual'));
        $response = $index->search('', $searchQuery);

        self::assertSame(5, $response->getSemanticHitCount());
        self::assertArrayNotHasKey('_vectors', $response->getHit(0));
    }

    public function testShowRankingScoreDetails(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setShowRankingScoreDetails(true);
        $response = $this->index->search('the', $searchQuery);
        $hit = $response->getHits()[0];

        self::assertArrayHasKey('_rankingScoreDetails', $hit);
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToFilter(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $filterAllFacets = function (array $facets): array {
            $filterOneFacet = function (array $facet): array {
                return array_filter(
                    $facet,
                    function (int $facetValue): bool { return 1 < $facetValue; },
                    ARRAY_FILTER_USE_BOTH
                );
            };

            return array_map($filterOneFacet, $facets);
        };

        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre']);
        $response = $this->index->search(
            null,
            $searchQuery,
            ['transformFacetDistribution' => $filterAllFacets]
        );

        $this->assertEstimatedPagination($response->toArray());
        self::assertSame($response->getRaw()['hits'], $response->getHits());
        self::assertNotSame($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
        self::assertCount(3, $response->getRaw()['facetDistribution']['genre']);
        self::assertCount(2, $response->getFacetDistribution()['genre']);
        self::assertSame(3, $response->getFacetDistribution()['genre']['romance']);
        self::assertSame(2, $response->getFacetDistribution()['genre']['fantasy']);
    }

    public function testSearchWithAttributesToSearchOn(): void
    {
        $this->index->updateSearchableAttributes(['comment', 'title'])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setAttributesToSearchOn(['comment']);
        $response = $this->index->search('the', $searchQuery);

        self::assertSame('The best book', $response->getHits()[0]['comment']);
    }

    public function testSearchWithShowRankingScore(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setShowRankingScore(true);
        $response = $this->index->search('the', $searchQuery);

        self::assertArrayHasKey('_rankingScore', $response->getHits()[0]);
    }

    public function testSearchWithRankingScoreThreshold(): void
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setShowRankingScore(true);
        $searchQuery->setRankingScoreThreshold(0.9);

        $response = $this->index->search('the', $searchQuery);

        self::assertArrayHasKey('_rankingScore', $response->getHits()[0]);
        self::assertSame(3, $response->getHitsCount());

        $searchQuery = new SearchQuery();
        $searchQuery->setShowRankingScore(true);
        $searchQuery->setRankingScoreThreshold(0.99);
        $response = $this->index->search('the', $searchQuery);

        self::assertSame(0, $response->getHitsCount());
    }

    public function testBasicSearchWithTransformFacetsDistributionOptionToMap(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

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

        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre']);
        $response = $this->index->search(
            null,
            $searchQuery,
            ['transformFacetDistribution' => $facetsToUpperFunc]
        );

        $this->assertEstimatedPagination($response->toArray());
        self::assertSame($response->getRaw()['hits'], $response->getHits());
        self::assertNotSame($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
        self::assertCount(3, $response->getFacetDistribution()['genre']);
        self::assertSame(3, $response->getFacetDistribution()['genre']['ROMANCE']);
        self::assertSame(2, $response->getFacetDistribution()['genre']['FANTASY']);
        self::assertSame(1, $response->getFacetDistribution()['genre']['ADVENTURE']);
    }

    public function testBasicSearchWithTransformFacetsDistributionOptionToOrder(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $facetsToUpperFunc = function (array $facets): array {
            $sortOneFacet = function (array $facet): array {
                ksort($facet);

                return $facet;
            };

            return array_map($sortOneFacet, $facets);
        };
        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['genre']);
        $response = $this->index->search(
            null,
            $searchQuery,
            ['transformFacetDistribution' => $facetsToUpperFunc]
        );

        $this->assertEstimatedPagination($response->toArray());
        self::assertSame($response->getRaw()['hits'], $response->getHits());
        self::assertSame('adventure', array_key_first($response->getFacetDistribution()['genre']));
        self::assertSame('romance', array_key_last($response->getFacetDistribution()['genre']));
        self::assertCount(3, $response->getFacetDistribution()['genre']);
        self::assertSame(3, $response->getFacetDistribution()['genre']['romance']);
        self::assertSame(2, $response->getFacetDistribution()['genre']['fantasy']);
        self::assertSame(1, $response->getFacetDistribution()['genre']['adventure']);
    }

    public function testSearchAndRetrieveFacetStats(): void
    {
        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateFilterableAttributes(['info.reviewNb']);

        $this->index->updateDocuments(self::NESTED_DOCUMENTS)->wait();
        $searchQuery = new SearchQuery();
        $searchQuery->setFacets(['info.reviewNb']);
        $response = $this->index->search(
            null,
            $searchQuery,
        );

        self::assertSame(['info.reviewNb' => ['min' => 50.0, 'max' => 1000.0]], $response->getFacetStats());
    }

    public function testSearchWithDistinctAttribute(): void
    {
        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateFilterableAttributes(['genre']);

        $this->index->updateDocuments(self::DOCUMENTS)->wait();
        $searchQuery = new SearchQuery();
        $searchQuery->setDistinct('genre');
        $response = $this->index->search(null, $searchQuery)->toArray();

        // Should have one document per unique genre
        // From DOCUMENTS: romance, adventure, fantasy, plus one document without genre
        self::assertCount(4, $response['hits']);

        // Extract genres from the results
        $genres = [];
        foreach ($response['hits'] as $hit) {
            $genre = $hit['genre'] ?? null;
            self::assertNotContains($genre, $genres, 'Each genre should appear only once in distinct results');
            $genres[] = $genre;
        }

        // Verify we have the expected unique genres
        $expectedGenres = ['romance', 'adventure', 'fantasy', null];
        foreach ($expectedGenres as $expectedGenre) {
            self::assertContains($expectedGenre, $genres, "Genre '{$expectedGenre}' should be present in distinct results");
        }
    }

    public function testSearchWithLocales(): void
    {
        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::DOCUMENTS);
        $this->index->updateLocalizedAttributes([['attributePatterns' => ['title', 'comment'], 'locales' => ['fra', 'eng']]])->wait();

        $searchQuery = new SearchQuery();
        $searchQuery->setLocales(['fra', 'eng']);
        $response = $this->index->search('french', $searchQuery)->toArray();

        self::assertCount(2, $response['hits']);
    }
}
