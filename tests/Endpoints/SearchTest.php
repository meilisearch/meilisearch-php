<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class SearchTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $this->index->waitForTask($promise['taskUid']);
    }

    public function testBasicSearch(): void
    {
        $response = $this->index->search('prince');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(2, $response->getEstimatedTotalHits());
        $this->assertCount(2, $response->getHits());

        $response = $this->index->search('prince', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['estimatedTotalHits']);
    }

    public function testBasicEmptySearch(): void
    {
        $response = $this->index->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertCount(7, $response->getHits());

        $response = $this->index->search('', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(7, $response['estimatedTotalHits']);
    }

    public function testBasicPlaceholderSearch(): void
    {
        $response = $this->index->search(null);

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertCount(\count(self::DOCUMENTS), $response->getHits());

        $response = $this->index->search(null, [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(\count(self::DOCUMENTS), $response['estimatedTotalHits']);
    }

    public function testSearchWithOptions(): void
    {
        $response = $this->index->search('prince', ['limit' => 1]);

        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('prince', ['limit' => 1], [
            'raw' => true,
        ]);

        $this->assertCount(1, $response['hits']);
    }

    public function testBasicSearchIfNoPrimaryKeyAndDocumentProvided(): void
    {
        $emptyIndex = $this->createEmptyIndex($this->safeIndexName('empty'));

        $res = $emptyIndex->search('prince');

        $this->assertArrayHasKey('hits', $res->toArray());
        $this->assertArrayHasKey('offset', $res->toArray());
        $this->assertArrayHasKey('limit', $res->toArray());
        $this->assertArrayHasKey('processingTimeMs', $res->toArray());
        $this->assertArrayHasKey('query', $res->toArray());
        $this->assertCount(0, $res->getHits());

        $res = $emptyIndex->search('prince', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $res);
        $this->assertArrayHasKey('offset', $res);
        $this->assertArrayHasKey('limit', $res);
        $this->assertArrayHasKey('processingTimeMs', $res);
        $this->assertArrayHasKey('query', $res);
        $this->assertSame(0, $res['estimatedTotalHits']);
    }

    public function testExceptionIfNoIndexWhenSearching(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('another-index'));
        $res = $index->delete();
        $index->waitForTask($res['taskUid']);

        $this->expectException(ApiException::class);

        $index->search('prince');
    }

    public function testParametersCropMarker(): void
    {
        $response = $this->index->search('blood', [
            'limit' => 1,
            'attributesToCrop' => ['title'],
            'cropLength' => 2,
        ]);

        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertSame('…Half-Blood…', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('blood', [
            'limit' => 1,
            'attributesToCrop' => ['title'],
            'cropLength' => 2,
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertSame('…Half-Blood…', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedCropMarker(): void
    {
        $response = $this->index->search('blood', [
            'limit' => 1,
            'attributesToCrop' => ['title'],
            'cropLength' => 3,
            'cropMarker' => '(ꈍᴗꈍ)',
        ]);

        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('blood', [
            'limit' => 1,
            'attributesToCrop' => ['title'],
            'cropLength' => 3,
            'cropMarker' => '(ꈍᴗꈍ)',
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertSame('(ꈍᴗꈍ)Half-Blood Prince', $response['hits'][0]['_formatted']['title']);
    }

    public function testSearchWithMatchingStrategyALL(): void
    {
        $response = $this->index->updateSearchableAttributes(['comment']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('another french book', [
            'matchingStrategy' => 'all',
        ]);

        $this->assertCount(1, $response->getHits());
    }

    public function testSearchWithMatchingStrategyLAST(): void
    {
        $response = $this->index->updateSearchableAttributes(['comment']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('french book', [
            'matchingStrategy' => 'last',
        ]);

        $this->assertCount(2, $response->getHits());
    }

    public function testParametersWithHighlightTag(): void
    {
        $response = $this->index->search('and', [
            'limit' => 1,
            'attributesToHighlight' => ['*'],
        ]);

        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertSame('Pride <em>and</em> Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('and', [
            'limit' => 1,
            'attributesToHighlight' => ['*'],
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertSame('Pride <em>and</em> Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersWithCustomizedHighlightTag(): void
    {
        $response = $this->index->search('and', [
            'limit' => 1,
            'attributesToHighlight' => ['*'],
            'highlightPreTag' => '(⊃｡•́‿•̀｡)⊃ ',
            'highlightPostTag' => ' ⊂(´• ω •`⊂)',
        ]);

        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('and', [
            'limit' => 1,
            'attributesToHighlight' => ['*'],
            'highlightPreTag' => '(⊃｡•́‿•̀｡)⊃ ',
            'highlightPostTag' => ' ⊂(´• ω •`⊂)',
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertSame('Pride (⊃｡•́‿•̀｡)⊃ and ⊂(´• ω •`⊂) Prejudice', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersArray(): void
    {
        $response = $this->index->updateFilterableAttributes(['title']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['id', 'title'],
            'attributesToCrop' => ['id', 'title'],
            'cropLength' => 6,
            'attributesToHighlight' => ['title'],
            'filter' => 'title = "Le Petit Prince"',
            'showMatchesPosition' => true,
        ]);

        $this->assertArrayHasKey('_matchesPosition', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        $this->assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['id', 'title'],
            'attributesToCrop' => ['id', 'title'],
            'cropLength' => 6,
            'attributesToHighlight' => ['title'],
            'filter' => 'title = "Le Petit Prince"',
            'showMatchesPosition' => true,
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_matchesPosition', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]['_matchesPosition']);
        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]['_matchesPosition']);
        $this->assertSame('Le Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersCanBeAStar(): void
    {
        $response = $this->index->updateFilterableAttributes(['title']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => ['*'],
            'cropLength' => 6,
            'attributesToHighlight' => ['*'],
            'filter' => 'title = "Le Petit Prince"',
            'showMatchesPosition' => true,
        ]);

        $this->assertArrayHasKey('_matchesPosition', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0)['_matchesPosition']);
        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertArrayHasKey('comment', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0)['_matchesPosition']);
        $this->assertSame('Le Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => ['*'],
            'cropLength' => 6,
            'attributesToHighlight' => ['*'],
            'filter' => 'title = "Le Petit Prince"',
            'showMatchesPosition' => true,
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_matchesPosition', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]['_matchesPosition']);
        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertArrayHasKey('comment', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]['_matchesPosition']);
        $this->assertSame('Le Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testSearchWithFilterCanBeInt(): void
    {
        $response = $this->index->updateFilterableAttributes(['id', 'genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'filter' => 'id < 12',
        ]);

        $this->assertSame(1, $response->getEstimatedTotalHits());
        $this->assertCount(1, $response->getHits());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('', [
            'filter' => 'genre = fantasy AND id < 12',
        ]);

        $this->assertSame(2, $response->getEstimatedTotalHits());
        $this->assertCount(2, $response->getHits());
        $this->assertSame(1, $response->getHit(0)['id']);
        $this->assertSame(4, $response->getHit(1)['id']);
    }

    public function testBasicSearchWithFacetDistribution(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'facets' => ['genre'],
        ]);
        $this->assertSame(2, $response->getHitsCount());
        $this->assertArrayHasKey('facetDistribution', $response->toArray());
        $this->assertArrayHasKey('genre', $response->getFacetDistribution());
        $this->assertSame($response->getFacetDistribution()['genre']['fantasy'], 1);
        $this->assertSame($response->getFacetDistribution()['genre']['adventure'], 1);

        $response = $this->index->search('prince', [
            'facets' => ['genre'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertArrayHasKey('facetDistribution', $response);
        $this->assertArrayHasKey('genre', $response['facetDistribution']);
        $this->assertSame($response['facetDistribution']['genre']['fantasy'], 1);
        $this->assertSame($response['facetDistribution']['genre']['adventure'], 1);
    }

    public function testBasicSearchWithFilters(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'filter' => [['genre = fantasy']],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'filter' => [['genre = fantasy']],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testBasicSearchWithMultipleFilter(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'filter' => ['genre = fantasy', ['genre = fantasy', 'genre = fantasy']],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'filter' => ['genre = fantasy', ['genre = fantasy', 'genre = fantasy']],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testCustomSearchWithFilterAndAttributesToRetrieve(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'filter' => [['genre = fantasy']],
            'attributesToRetrieve' => ['id', 'title'],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);
        $this->assertArrayHasKey('id', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0));

        $response = $this->index->search('prince', [
            'filter' => [['genre = fantasy']],
            'attributesToRetrieve' => ['id', 'title'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
        $this->assertArrayHasKey('id', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]);
    }

    public function testSearchSortWithString(): void
    {
        $response = $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ]);
        $this->index->waitForTask($response['taskUid']);
        $response = $this->index->updateSortableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'sort' => ['genre:asc'],
        ]);
        $this->assertSame(2, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(456, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'sort' => ['genre:asc'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(456, $response['hits'][0]['id']);
    }

    public function testSearchSortWithInt(): void
    {
        $response = $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ]);
        $this->index->waitForTask($response['taskUid']);
        $response = $this->index->updateSortableAttributes(['id']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'sort' => ['id:asc'],
        ]);
        $this->assertSame(2, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'sort' => ['id:asc'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchSortWithMultipleParameter(): void
    {
        $response = $this->index->updateRankingRules([
            'words',
            'typo',
            'sort',
            'proximity',
            'attribute',
            'exactness',
        ]);
        $this->index->waitForTask($response['taskUid']);
        $response = $this->index->updateSortableAttributes(['id', 'title']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('prince', [
            'sort' => ['id:asc', 'title:asc'],
        ]);
        $this->assertSame(2, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetDistribution', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'sort' => ['id:asc', 'title:asc'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertArrayNotHasKey('facetDistribution', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchWithPhraseSearch(): void
    {
        $response = $this->index->rawSearch('coco "harry"');

        $this->assertCount(1, $response['hits']);
        $this->assertEquals(4, $response['hits'][0]['id']);
        $this->assertEquals('Harry Potter and the Half-Blood Prince', $response['hits'][0]['title']);
    }

    public function testBasicSerachWithRawSearch(): void
    {
        $response = $this->index->rawSearch('prince');

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertCount(2, $response['hits']);
        $this->assertEquals('Le Petit Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithRawOption(): void
    {
        $response = $this->index->search('prince', [], ['raw' => true]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertCount(2, $response['hits']);
    }

    public function testBasicSearchWithTransformHitsOptionToFilter(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search('prince', [], $options = ['transformHits' => $keepLePetitPrinceFunc]);

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame('Le Petit Prince', $response->getHit(0)['title']);
        $this->assertSame(2, $response->getEstimatedTotalHits());
        $this->assertSame(1, $response->getHitsCount());
        $this->assertSame(1, $response->count());
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

        $response = $this->index->search('prince', [], ['transformHits' => $titlesToUpperCaseFunc]);

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(2, $response->getEstimatedTotalHits());
        $this->assertSame(2, $response->getHitsCount());
        $this->assertCount(2, $response->getHits());
        $this->assertSame('LE PETIT PRINCE', $response->getHits()[0]['title']);
    }

    public function testBasicSearchCannotBeFilteredOnRawResult(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search('prince', [], [
            'raw' => true,
            'transformHits' => $keepLePetitPrinceFunc,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertCount(2, $response['hits']);
    }

    public function testBasicSearchCanBeFilteredOnRawResultIfUsingToArray(): void
    {
        $keepLePetitPrinceFunc = function (array $hits): array {
            return array_filter(
                $hits,
                function (array $hit): bool { return 'Le Petit Prince' === $hit['title']; }
            );
        };

        $response = $this->index->search('prince', [], ['transformHits' => $keepLePetitPrinceFunc])->toArray();

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['estimatedTotalHits']);
        $this->assertCount(1, $response['hits']);
        $this->assertEquals('Le Petit Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithFacetsOption(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search(
            'prince',
            ['facets' => ['genre']]
        );

        $this->assertCount(2, $response->getFacetDistribution()['genre']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['adventure']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['fantasy']);
        $this->assertCount(2, $response->getRaw()['facetDistribution']['genre']);
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertEquals($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
    }

    public function testBasicSearchWithFacetsOptionAndMultipleFacets(): void
    {
        $response = $this->index->addDocuments([['id' => 32, 'title' => 'The Witcher', 'genre' => 'adventure', 'adaptation' => 'video game']]);
        $this->index->waitForTask($response['taskUid']);
        $response = $this->index->updateFilterableAttributes(['genre', 'adaptation']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search(
            'witch',
            ['facets' => ['genre', 'adaptation']]
        );

        $this->assertCount(1, $response->getFacetDistribution()['genre']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['adventure']);
        $this->assertCount(1, $response->getFacetDistribution()['adaptation']);
        $this->assertEquals(1, $response->getFacetDistribution()['adaptation']['video game']);
        $this->assertCount(1, $response->getRaw()['facetDistribution']['adaptation']);
        $this->assertCount(1, $response->getRaw()['facetDistribution']['genre']);
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertEquals($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToFilter(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

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
            null,
            ['facets' => ['genre']],
            ['transformFacetDistribution' => $filterAllFacets]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
        $this->assertCount(3, $response->getRaw()['facetDistribution']['genre']);
        $this->assertCount(2, $response->getFacetDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetDistribution()['genre']['romance']);
        $this->assertEquals(2, $response->getFacetDistribution()['genre']['fantasy']);
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToMap(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

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
            null,
            ['facets' => ['genre']],
            ['transformFacetDistribution' => $facetsToUpperFunc]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetDistribution'], $response->getFacetDistribution());
        $this->assertCount(3, $response->getFacetDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetDistribution()['genre']['ROMANCE']);
        $this->assertEquals(2, $response->getFacetDistribution()['genre']['FANTASY']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['ADVENTURE']);
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToOder(): void
    {
        $response = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($response['taskUid']);

        $facetsToUpperFunc = function (array $facets): array {
            $sortOneFacet = function (array $facet): array {
                ksort($facet);

                return $facet;
            };

            return array_map($sortOneFacet, $facets);
        };

        $response = $this->index->search(
            null,
            ['facets' => ['genre']],
            ['transformFacetDistribution' => $facetsToUpperFunc]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertEquals('adventure', array_key_first($response->getFacetDistribution()['genre']));
        $this->assertEquals('romance', array_key_last($response->getFacetDistribution()['genre']));
        $this->assertCount(3, $response->getFacetDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetDistribution()['genre']['romance']);
        $this->assertEquals(2, $response->getFacetDistribution()['genre']['fantasy']);
        $this->assertEquals(1, $response->getFacetDistribution()['genre']['adventure']);
    }
}
