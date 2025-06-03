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

        $index->updateFacetSearch(false)->wait();

        self::assertFalse($index->getFacetSearch());
    }

    public function testResetFacetSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFacetSearch(false)->wait();
        $index->resetFacetSearch()->wait();

        self::assertTrue($index->getFacetSearch());
    }
}
