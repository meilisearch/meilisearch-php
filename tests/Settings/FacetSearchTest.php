<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FacetSearchTest extends TestCase
{
    public function testGetDefaultFacetSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $facetSearch = $index->getFacetSearch();

        self::assertTrue($facetSearch);
    }

    public function testUpdateFacetSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFacetSearch(false);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $facetSearch = $index->getFacetSearch();
        self::assertFalse($facetSearch);
    }

    public function testResetFacetSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFacetSearch(false);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetFacetSearch();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $facetSearch = $index->getFacetSearch();
        self::assertTrue($facetSearch);
    }
}
