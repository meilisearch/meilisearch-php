<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class FacetSearchTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::DOCUMENTS);
        $promise = $this->index->updateFilterableAttributes(['genre']);
        $this->index->waitForTask($promise['taskUid']);
    }

    public function testBasicSearchWithFilters(): void
    {
        $response = $this->index->search('prince', ['facets' => ['genre']]);

        $this->assertSame(array_keys($response->getFacetDistribution()['genre']), [
            'adventure', 'fantasy',
        ]);

        $response = $this->index->facetSearch(
            (new FacetSearchQuery())
                ->setFacetQuery('fa')
                ->setFacetName('genre')
                ->setQuery('prince')
        );

        $this->assertSame(array_column($response->getFacetHits(), 'value'), ['fantasy']);
    }
}
