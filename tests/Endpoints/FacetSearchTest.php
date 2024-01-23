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

        self::assertSame(['adventure', 'fantasy'], array_keys($response->getFacetDistribution()['genre']));

        $response = $this->index->facetSearch(
            (new FacetSearchQuery())
                ->setFacetQuery('fa')
                ->setFacetName('genre')
                ->setQuery('prince')
        );

        self::assertSame(['fantasy'], array_column($response->getFacetHits(), 'value'));
    }
}
