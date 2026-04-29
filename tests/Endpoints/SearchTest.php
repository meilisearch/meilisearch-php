<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\HybridSearchOptions;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Index;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class SearchTest extends TestCase
{
    private Index $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::DOCUMENTS)->wait();
    }

    public function testBasicSearch(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('prince'));

        $this->assertEstimatedPagination($response->toArray());
        self::assertCount(2, $response->getHits());

        self::assertSame(2, $response->getEstimatedTotalHits());
        self::assertSame(0, $response->getOffset());
        self::assertSame(20, $response->getLimit());

        self::assertNull($response->getHitsPerPage());
        self::assertNull($response->getPage());
        self::assertNull($response->getTotalPages());
        self::assertNull($response->getTotalHits());

        $response = $this->index->search((new SearchQuery())->setQuery('prince'), [
            'raw' => true,
        ]);

        $this->assertEstimatedPagination($response);
        self::assertSame(2, $response['estimatedTotalHits']);
    }

    public function testBasicSearchWithFinitePagination(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('prince')->setHitsPerPage(2));

        $this->assertFinitePagination($response->toArray());
        self::assertCount(2, $response->getHits());

        self::assertSame(2, $response->getHitsPerPage());
        self::assertSame(1, $response->getPage());
        self::assertSame(1, $response->getTotalPages());
        self::assertSame(2, $response->getTotalHits());

        self::assertNull($response->getEstimatedTotalHits());
        self::assertNull($response->getOffset());
        self::assertNull($response->getLimit());

        $response = $this->index->search((new SearchQuery())->setQuery('prince')->setHitsPerPage(2), [
            'raw' => true,
        ]);

        $this->assertFinitePagination($response);
    }

    public function testBasicEmptySearch(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery(''));

        $this->assertEstimatedPagination($response->toArray());
        self::assertCount(7, $response->getHits());

        $response = $this->index->search((new SearchQuery())->setQuery(''), [
            'raw' => true,
        ]);

        $this->assertEstimatedPagination($response);
        self::assertSame(7, $response['estimatedTotalHits']);
    }

    public function testSearchWithSearchQueryObjectAndOptions(): void
    {
        $response = $this->index->search(
            (new SearchQuery())->setQuery('prince'),
            ['raw' => true]
        );

        self::assertIsArray($response);
        self::assertArrayHasKey('hits', $response);
    }

    public function testBasicPlaceholderSearch(): void
    {
        $response = $this->index->search(new SearchQuery());

        $this->assertEstimatedPagination($response->toArray());
        self::assertCount(\count(self::DOCUMENTS), $response->getHits());

        $response = $this->index->search(new SearchQuery(), [
            'raw' => true,
        ]);

        $this->assertEstimatedPagination($response);
        self::assertSame(\count(self::DOCUMENTS), $response['estimatedTotalHits']);
    }

    public function testSearchWithOptions(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('prince')->setLimit(1));

        self::assertCount(1, $response->getHits());

        $response = $this->index->search((new SearchQuery())->setQuery('prince')->setLimit(1), [
            'raw' => true,
        ]);

        self::assertCount(1, $response['hits']);
    }

    public function testBasicSearchIfNoPrimaryKeyAndDocumentProvided(): void
    {
        $emptyIndex = $this->createEmptyIndex($this->safeIndexName('empty'));

        $res = $emptyIndex->search((new SearchQuery())->setQuery('prince'));

        $this->assertEstimatedPagination($res->toArray());
        self::assertCount(0, $res->getHits());

        $res = $emptyIndex->search((new SearchQuery())->setQuery('prince'), [
            'raw' => true,
        ]);

        $this->assertEstimatedPagination($res);
        self::assertSame(0, $res['estimatedTotalHits']);
    }

    public function testExceptionIfNoIndexWhenSearching(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movie-1'));

        $index->delete()->wait();

        $this->expectException(ApiException::class);

        $index->search((new SearchQuery())->setQuery('prince'));
    }

    public function testParametersCropMarker(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('blood')
            ->setLimit(1)
            ->setAttributesToCrop(['title'])
            ->setCropLength(2));

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('…Half-Blood…', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('blood')
            ->setLimit(1)
            ->setAttributesToCrop(['title'])
            ->setCropLength(2), [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('…Half-Blood…', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedCropMarker(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('blood')
            ->setLimit(1)
            ->setAttributesToCrop(['title'])
            ->setCropLength(3)
            ->setCropMarker('(ꈍᴗꈍ)'));

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('blood')
            ->setLimit(1)
            ->setAttributesToCrop(['title'])
            ->setCropLength(3)
            ->setCropMarker('(ꈍᴗꈍ)'), [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response['hits'][0]['_formatted']['title']);
    }

    public function testSearchWithMatchingStrategyALL(): void
    {
        $this->index->updateSearchableAttributes(['comment'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('another french book')
            ->setMatchingStrategy('all'));

        self::assertCount(1, $response->getHits());
    }

    public function testSearchWithMatchingStrategyLAST(): void
    {
        $this->index->updateSearchableAttributes(['comment'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('french book')
            ->setMatchingStrategy('last'));

        self::assertCount(2, $response->getHits());
    }

    public function testParametersWithHighlightTag(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('and')
            ->setLimit(1)
            ->setAttributesToHighlight(['*']));

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('Pride <em>and</em> Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('and')
            ->setLimit(1)
            ->setAttributesToHighlight(['*']), [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('Pride <em>and</em> Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedHighlightTag(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('and')
            ->setLimit(1)
            ->setAttributesToHighlight(['*'])
            ->setHighlightPreTag('(⊃｡•́‿•̀｡)⊃ ')
            ->setHighlightPostTag(' ⊂(´• ω •`⊂)'));

        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('and')
            ->setLimit(1)
            ->setAttributesToHighlight(['*'])
            ->setHighlightPreTag('(⊃｡•́‿•̀｡)⊃ ')
            ->setHighlightPostTag(' ⊂(´• ω •`⊂)'), [
            'raw' => true,
        ]);

        self::assertArrayHasKey('_formatted', $response['hits'][0]);
        self::assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersArray(): void
    {
        $this->index->updateFilterableAttributes(['title'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setLimit(5)
            ->setOffset(0)
            ->setAttributesToRetrieve(['id', 'title'])
            ->setAttributesToCrop(['id', 'title'])
            ->setCropLength(6)
            ->setAttributesToHighlight(['title'])
            ->setFilter('title = "Le Petit Prince"')
            ->setShowMatchesPosition(true));

        self::assertArrayHasKey('_matchesPosition', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setLimit(5)
            ->setOffset(0)
            ->setAttributesToRetrieve(['id', 'title'])
            ->setAttributesToCrop(['id', 'title'])
            ->setCropLength(6)
            ->setAttributesToHighlight(['title'])
            ->setFilter('title = "Le Petit Prince"')
            ->setShowMatchesPosition(true), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setLimit(5)
            ->setOffset(0)
            ->setAttributesToRetrieve(['*'])
            ->setAttributesToCrop(['*'])
            ->setCropLength(6)
            ->setAttributesToHighlight(['*'])
            ->setFilter('title = "Le Petit Prince"')
            ->setShowMatchesPosition(true));

        self::assertArrayHasKey('_matchesPosition', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        self::assertArrayHasKey('_formatted', $response->getHit(0));
        self::assertArrayHasKey('comment', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        self::assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setLimit(5)
            ->setOffset(0)
            ->setAttributesToRetrieve(['*'])
            ->setAttributesToCrop(['*'])
            ->setCropLength(6)
            ->setAttributesToHighlight(['*'])
            ->setFilter('title = "Le Petit Prince"')
            ->setShowMatchesPosition(true), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter('id < 12'));

        self::assertSame(1, $response->getEstimatedTotalHits());
        self::assertCount(1, $response->getHits());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('')
            ->setFilter('genre = fantasy AND id < 12'));

        self::assertSame(2, $response->getEstimatedTotalHits());
        self::assertCount(2, $response->getHits());
        self::assertSame(1, $response->getHit(0)['id']);
        self::assertSame(4, $response->getHit(1)['id']);
    }

    public function testBasicSearchWithFacetDistribution(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFacets(['genre']));
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayHasKey('genre', $response->getFacetDistribution());
        self::assertSame($response->getFacetDistribution()['genre']['fantasy'], 1);
        self::assertSame($response->getFacetDistribution()['genre']['adventure'], 1);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFacets(['genre']), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter([['genre = fantasy']]));
        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter([['genre = fantasy']]), [
            'raw' => true,
        ]);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testBasicSearchWithMultipleFilter(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter(['genre = fantasy', ['genre = fantasy', 'genre = fantasy']]));
        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter(['genre = fantasy', ['genre = fantasy', 'genre = fantasy']]), [
            'raw' => true,
        ]);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testCustomSearchWithFilterAndAttributesToRetrieve(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter([['genre = fantasy']])
            ->setAttributesToRetrieve(['id', 'title']));
        self::assertSame(1, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);
        self::assertArrayHasKey('id', $response->getHit(0));
        self::assertArrayHasKey('title', $response->getHit(0));
        self::assertArrayNotHasKey('comment', $response->getHit(0));

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setFilter([['genre = fantasy']])
            ->setAttributesToRetrieve(['id', 'title']), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['genre:asc']));
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(456, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['genre:asc']), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['id:asc']));
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['id:asc']), [
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

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['id:asc', 'title:asc']));
        self::assertSame(2, $response->getHitsCount());
        self::assertArrayNotHasKey('facetDistribution', $response->getRaw());
        self::assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search((new SearchQuery())->setQuery('prince')
            ->setSort(['id:asc', 'title:asc']), [
            'raw' => true,
        ]);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertArrayNotHasKey('facetDistribution', $response);
        self::assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchWithPhraseSearch(): void
    {
        $response = $this->index->rawSearch((new SearchQuery())->setQuery('coco "harry"'));

        self::assertCount(1, $response['hits']);
        self::assertSame(4, $response['hits'][0]['id']);
        self::assertSame('Harry Potter and the Half-Blood Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithRawSearch(): void
    {
        $response = $this->index->rawSearch((new SearchQuery())->setQuery('prince'));

        $this->assertEstimatedPagination($response);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertCount(2, $response['hits']);
        self::assertSame('Le Petit Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithRawOption(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('prince'), ['raw' => true]);

        $this->assertEstimatedPagination($response);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertCount(2, $response['hits']);
    }

    public function testBasicSearchWithTransformHitsOptionToFilter(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search((new SearchQuery())->setQuery('prince'), $options = ['transformHits' => $keepLePetitPrinceFunc]);

        $this->assertEstimatedPagination($response->toArray());
        self::assertSame('Le Petit Prince', $response->getHit(0)['title']);
        self::assertSame(2, $response->getEstimatedTotalHits());
        self::assertSame(1, $response->getHitsCount());
        self::assertCount(1, $response);
    }

    public function testBasicSearchWithTransformHitsOptionToMap(): void
    {
        $titlesToUpperCaseFunc = function (array $hits): array {
            return array_map(
                function (array $hit) {
                    $hit['title'] = strtoupper($hit['title']);

                    return $hit;
                },
                $hits
            );
        };

        $response = $this->index->search((new SearchQuery())->setQuery('prince'), ['transformHits' => $titlesToUpperCaseFunc]);

        $this->assertEstimatedPagination($response->toArray());
        self::assertSame(2, $response->getEstimatedTotalHits());
        self::assertSame(2, $response->getHitsCount());
        self::assertCount(2, $response->getHits());
        self::assertSame('LE PETIT PRINCE', $response->getHits()[0]['title']);
    }

    public function testBasicSearchCannotBeFilteredOnRawResult(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search((new SearchQuery())->setQuery('prince'), [
            'raw' => true,
            'transformHits' => $keepLePetitPrinceFunc,
        ]);

        $this->assertEstimatedPagination($response);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertCount(2, $response['hits']);
    }

    public function testBasicSearchCanBeFilteredOnRawResultIfUsingToArray(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search((new SearchQuery())->setQuery('prince'), ['transformHits' => $keepLePetitPrinceFunc])->toArray();

        $this->assertEstimatedPagination($response);
        self::assertSame(2, $response['estimatedTotalHits']);
        self::assertCount(1, $response['hits']);
        self::assertSame('Le Petit Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithFacetsOption(): void
    {
        $this->index->updateFilterableAttributes(['genre'])->wait();

        $response = $this->index->search(
            (new SearchQuery())->setQuery('prince')
            ->setFacets(['genre'])
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

        $response = $this->index->search(
            (new SearchQuery())->setQuery('witch')
            ->setFacets(['genre', 'adaptation'])
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

        $response = $index->search(
            (new SearchQuery())->setQuery('')
            ->setVector([-0.5, 0.3, 0.85])
            ->setHybrid((new HybridSearchOptions())->setSemanticRatio(1.0)->setEmbedder('manual'))
        );

        self::assertSame(5, $response->getSemanticHitCount());
        self::assertArrayNotHasKey('_vectors', $response->getHit(0));

        $response = $this->index->search(
            (new SearchQuery())->setQuery('')
            ->setVector([-0.5, 0.3, 0.85])
            ->setRetrieveVectors(true)
            ->setHybrid((new HybridSearchOptions())->setSemanticRatio(1.0)->setEmbedder('manual'))
        );

        self::assertSame(5, $response->getSemanticHitCount());
        self::assertArrayHasKey('_vectors', $response->getHit(0));
    }

    public function testShowRankingScoreDetails(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));

        $response = $this->index->search((new SearchQuery())->setQuery('the')->setShowRankingScoreDetails(true));
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

        $response = $this->index->search(
            (new SearchQuery())->setFacets(['genre']),
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

        $response = $this->index->search((new SearchQuery())->setQuery('the')->setAttributesToSearchOn(['comment']));

        self::assertSame('The best book', $response->getHits()[0]['comment']);
    }

    public function testSearchWithShowRankingScore(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('the')->setShowRankingScore(true));

        self::assertArrayHasKey('_rankingScore', $response->getHits()[0]);
    }

    public function testSearchWithRankingScoreThreshold(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('the')->setShowRankingScore(true)->setRankingScoreThreshold(0.9));

        self::assertArrayHasKey('_rankingScore', $response->getHits()[0]);
        self::assertSame(3, $response->getHitsCount());

        $response = $this->index->search((new SearchQuery())->setQuery('the')->setShowRankingScore(true)->setRankingScoreThreshold(0.99));

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

        $response = $this->index->search(
            (new SearchQuery())->setFacets(['genre']),
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

        $response = $this->index->search(
            (new SearchQuery())->setFacets(['genre']),
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

        $response = $this->index->search(
            (new SearchQuery())->setFacets(['info.reviewNb']),
        );

        self::assertSame(['info.reviewNb' => ['min' => 50.0, 'max' => 1000.0]], $response->getFacetStats());
    }

    public function testSearchWithDistinctAttribute(): void
    {
        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateFilterableAttributes(['genre']);

        $this->index->updateDocuments(self::DOCUMENTS)->wait();

        $response = $this->index->search((new SearchQuery())->setDistinct('genre'))->toArray();

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

        $response = $this->index->search((new SearchQuery())->setQuery('french')
            ->setLocales(['fra', 'eng']))
            ->toArray();

        self::assertCount(2, $response['hits']);
    }
}
