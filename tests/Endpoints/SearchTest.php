<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class SearchTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->client->createIndex('index');
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $this->index->waitForPendingUpdate($promise['updateId']);
    }

    public function testBasicSearch(): void
    {
        $response = $this->index->search('prince');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(2, $response->getNbHits());
        $this->assertCount(2, $response->getHits());

        $response = $this->index->search('prince', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['nbHits']);
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
        $this->assertSame(7, $response['nbHits']);
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
        $this->assertSame(\count(self::DOCUMENTS), $response['nbHits']);
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
        $emptyIndex = $this->client->createIndex('empty');

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
        $this->assertSame(0, $res['nbHits']);
    }

    public function testExceptionIfNoIndexWhenSearching(): void
    {
        $index = $this->client->createIndex('another-index');
        $index->delete();

        $this->expectException(ApiException::class);

        $index->search('prince');
    }

    public function testParametersArray(): void
    {
        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['id', 'title'],
            'attributesToCrop' => ['id', 'title'],
            'cropLength' => 6,
            'attributesToHighlight' => ['title'],
            'filters' => 'title = "Le Petit Prince"',
            'matches' => true,
        ]);

        $this->assertArrayHasKey('_matchesInfo', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0)['_matchesInfo']);
        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0)['_matchesInfo']);
        $this->assertSame('Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['id', 'title'],
            'attributesToCrop' => ['id', 'title'],
            'cropLength' => 6,
            'attributesToHighlight' => ['title'],
            'filters' => 'title = "Le Petit Prince"',
            'matches' => true,
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_matchesInfo', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]['_matchesInfo']);
        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]['_matchesInfo']);
        $this->assertSame('Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testParametersCanBeAStar(): void
    {
        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => ['*'],
            'cropLength' => 6,
            'attributesToHighlight' => ['*'],
            'filters' => 'title = "Le Petit Prince"',
            'matches' => true,
        ]);

        $this->assertArrayHasKey('_matchesInfo', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0)['_matchesInfo']);
        $this->assertArrayHasKey('_formatted', $response->getHit(0));
        $this->assertArrayHasKey('comment', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0)['_matchesInfo']);
        $this->assertSame('Petit <em>Prince</em>', $response->getHit(0)['_formatted']['title']);

        $response = $this->index->search('prince', [
            'limit' => 5,
            'offset' => 0,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => ['*'],
            'cropLength' => 6,
            'attributesToHighlight' => ['*'],
            'filters' => 'title = "Le Petit Prince"',
            'matches' => true,
        ], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('_matchesInfo', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]['_matchesInfo']);
        $this->assertArrayHasKey('_formatted', $response['hits'][0]);
        $this->assertArrayHasKey('comment', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]['_matchesInfo']);
        $this->assertSame('Petit <em>Prince</em>', $response['hits'][0]['_formatted']['title']);
    }

    public function testBasicSearchWithFacetsDistribution(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search('prince', [
            'facetsDistribution' => ['genre'],
        ]);
        $this->assertSame(2, $response->getHitsCount());
        $this->assertArrayHasKey('facetsDistribution', $response->toArray());
        $this->assertArrayHasKey('exhaustiveFacetsCount', $response->toArray());
        $this->assertArrayHasKey('genre', $response->getFacetsDistribution());
        $this->assertTrue($response->getExhaustiveFacetsCount());
        $this->assertSame($response->getFacetsDistribution()['genre']['fantasy'], 1);
        $this->assertSame($response->getFacetsDistribution()['genre']['adventure'], 1);
        $this->assertSame($response->getFacetsDistribution()['genre']['romance'], 0);

        $response = $this->index->search('prince', [
            'facetsDistribution' => ['genre'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(2, $response['nbHits']);
        $this->assertArrayHasKey('facetsDistribution', $response);
        $this->assertArrayHasKey('exhaustiveFacetsCount', $response);
        $this->assertArrayHasKey('genre', $response['facetsDistribution']);
        $this->assertTrue($response['exhaustiveFacetsCount']);
        $this->assertSame($response['facetsDistribution']['genre']['fantasy'], 1);
        $this->assertSame($response['facetsDistribution']['genre']['adventure'], 1);
        $this->assertSame($response['facetsDistribution']['genre']['romance'], 0);
    }

    public function testBasicSearchWithFacetFilters(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search('prince', [
            'facetFilters' => [['genre:fantasy']],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetsDistribution', $response->getRaw());
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'facetFilters' => [['genre:fantasy']],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['nbHits']);
        $this->assertArrayNotHasKey('facetsDistribution', $response);
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testBasicSearchWithMultipleFacetFilters(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search('prince', [
            'facetFilters' => ['genre:fantasy', ['genre:fantasy', 'genre:fantasy']],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetsDistribution', $response->getRaw());
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);

        $response = $this->index->search('prince', [
            'facetFilters' => ['genre:fantasy', ['genre:fantasy', 'genre:fantasy']],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['nbHits']);
        $this->assertArrayNotHasKey('facetsDistribution', $response);
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testCustomSearchWithFacetFiltersAndAttributesToRetrieve(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search('prince', [
            'facetFilters' => [['genre:fantasy']],
            'attributesToRetrieve' => ['id', 'title'],
        ]);
        $this->assertSame(1, $response->getHitsCount());
        $this->assertArrayNotHasKey('facetsDistribution', $response->getRaw());
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response->getRaw());
        $this->assertSame(4, $response->getHit(0)['id']);
        $this->assertArrayHasKey('id', $response->getHit(0));
        $this->assertArrayHasKey('title', $response->getHit(0));
        $this->assertArrayNotHasKey('comment', $response->getHit(0));

        $response = $this->index->search('prince', [
            'facetFilters' => [['genre:fantasy']],
            'attributesToRetrieve' => ['id', 'title'],
        ], [
            'raw' => true,
        ]);
        $this->assertSame(1, $response['nbHits']);
        $this->assertArrayNotHasKey('facetsDistribution', $response);
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
        $this->assertArrayHasKey('id', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]);
    }

    public function testBasicSerachWithRawSearch(): void
    {
        $response = $this->index->rawSearch('prince');

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertSame(2, $response['nbHits']);
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
        $this->assertSame(2, $response['nbHits']);
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
        $this->assertSame(2, $response->getNbHits());
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
        $this->assertSame(2, $response->getNbHits());
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
        $this->assertSame(2, $response['nbHits']);
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
        $this->assertSame(2, $response['nbHits']);
        $this->assertCount(1, $response['hits']);
        $this->assertEquals('Le Petit Prince', $response['hits'][0]['title']);
    }

    public function testBasicSearchWithRemoveZeroFacetsOption(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search(
            'prince',
            ['facetsDistribution' => ['genre']],
            ['removeZeroFacets' => true]
        );

        $this->assertCount(2, $response->getFacetsDistribution()['genre']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['adventure']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['fantasy']);
        $this->assertCount(3, $response->getRaw()['facetsDistribution']['genre']);
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetsDistribution'], $response->getFacetsDistribution());
    }

    public function testBasicSearchWithRemoveZeroFacetsOptionAndMultipleFacets(): void
    {
        $response = $this->index->addDocuments([['id' => 32, 'title' => 'The Witcher', 'genre' => 'adventure', 'adaptation' => 'video game']]);
        $this->index->waitForPendingUpdate($response['updateId']);
        $response = $this->index->updateAttributesForFaceting(['genre', 'adaptation']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $response = $this->index->search(
            'prince',
            ['facetsDistribution' => ['genre', 'adaptation']],
            ['removeZeroFacets' => true]
        );

        $this->assertCount(2, $response->getFacetsDistribution()['genre']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['adventure']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['fantasy']);
        $this->assertEquals([], $response->getFacetsDistribution()['adaptation']);
        $this->assertCount(1, $response->getRaw()['facetsDistribution']['adaptation']);
        $this->assertCount(3, $response->getRaw()['facetsDistribution']['genre']);
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetsDistribution'], $response->getFacetsDistribution());
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToFilter(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

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
            ['facetsDistribution' => ['genre']],
            ['transformFacetsDistribution' => $filterAllFacets]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetsDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetsDistribution'], $response->getFacetsDistribution());
        $this->assertCount(3, $response->getRaw()['facetsDistribution']['genre']);
        $this->assertCount(2, $response->getFacetsDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetsDistribution()['genre']['romance']);
        $this->assertEquals(2, $response->getFacetsDistribution()['genre']['fantasy']);
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToMap(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

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
            ['facetsDistribution' => ['genre']],
            ['transformFacetsDistribution' => $facetsToUpperFunc]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetsDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertNotEquals($response->getRaw()['facetsDistribution'], $response->getFacetsDistribution());
        $this->assertCount(3, $response->getFacetsDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetsDistribution()['genre']['ROMANCE']);
        $this->assertEquals(2, $response->getFacetsDistribution()['genre']['FANTASY']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['ADVENTURE']);
    }

    public function testBasicSearchWithTransformFacetsDritributionOptionToOder(): void
    {
        $response = $this->index->updateAttributesForFaceting(['genre']);
        $this->index->waitForPendingUpdate($response['updateId']);

        $facetsToUpperFunc = function (array $facets): array {
            $sortOneFacet = function (array $facet): array {
                ksort($facet);

                return $facet;
            };

            return array_map($sortOneFacet, $facets);
        };

        $response = $this->index->search(
            null,
            ['facetsDistribution' => ['genre']],
            ['transformFacetsDistribution' => $facetsToUpperFunc]
        );

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('facetsDistribution', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertEquals($response->getRaw()['hits'], $response->getHits());
        $this->assertEquals('adventure', array_key_first($response->getFacetsDistribution()['genre']));
        $this->assertEquals('romance', array_key_last($response->getFacetsDistribution()['genre']));
        $this->assertCount(3, $response->getFacetsDistribution()['genre']);
        $this->assertEquals(3, $response->getFacetsDistribution()['genre']['romance']);
        $this->assertEquals(2, $response->getFacetsDistribution()['genre']['fantasy']);
        $this->assertEquals(1, $response->getFacetsDistribution()['genre']['adventure']);
    }
}
