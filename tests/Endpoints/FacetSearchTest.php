<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Index;
use Tests\TestCase;

final class FacetSearchTest extends TestCase
{
    private Index $index;

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateDocuments(self::DOCUMENTS);
        $this->index->updateFilterableAttributes(['genre'])->wait();
    }

    public function testBasicSearchWithFilters(): void
    {
        $response = $this->index->search((new SearchQuery())->setQuery('prince')->setFacets(['genre']));

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
