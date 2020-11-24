<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
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

        $this->assertSame(1, \count($response['hits']));
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

        $this->expectException(HTTPRequestException::class);

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
        $this->assertSame(2, $response->getMatches());
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
        $this->assertSame(1, $response->getMatches());
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
        $this->assertSame(1, $response->getMatches());
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
        $this->assertSame(1, $response->getMatches());
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
}
