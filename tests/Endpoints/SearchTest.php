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

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertCount(2, $response['hits']);
    }

    public function testBasicEmptySearch(): void
    {
        $response = $this->index->search('');

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertCount(7, $response['hits']);
    }

    public function testBasicPlaceholderSearch(): void
    {
        $response = $this->index->search(null);

        $this->assertArrayHasKey('hits', $response);
        $this->assertArrayHasKey('offset', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('processingTimeMs', $response);
        $this->assertArrayHasKey('query', $response);
        $this->assertCount(\count(self::DOCUMENTS), $response['hits']);
    }

    public function testSearchWithOptions(): void
    {
        $response = $this->index->search('prince', ['limit' => 1]);

        $this->assertCount(1, $response['hits']);
    }

    public function testBasicSearchIfNoPrimaryKeyAndDocumentProvided(): void
    {
        $emptyIndex = $this->client->createIndex('empty');

        $res = $emptyIndex->search('prince');

        $this->assertArrayHasKey('hits', $res);
        $this->assertArrayHasKey('offset', $res);
        $this->assertArrayHasKey('limit', $res);
        $this->assertArrayHasKey('processingTimeMs', $res);
        $this->assertArrayHasKey('query', $res);
        $this->assertCount(0, $res['hits']);
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
        $this->assertSame(1, $response['nbHits']);
        $this->assertArrayNotHasKey('facetsDistribution', $response);
        $this->assertArrayNotHasKey('exhaustiveFacetsCount', $response);
        $this->assertSame(4, $response['hits'][0]['id']);
        $this->assertArrayHasKey('id', $response['hits'][0]);
        $this->assertArrayHasKey('title', $response['hits'][0]);
        $this->assertArrayNotHasKey('comment', $response['hits'][0]);
    }
}
