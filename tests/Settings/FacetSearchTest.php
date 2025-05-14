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

        $task = $index->updateFacetSearch(false);
        $index->waitForTask($task['taskUid']);

        self::assertFalse($index->getFacetSearch());
    }

    public function testResetFacetSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updateFacetSearch(false);
        $index->waitForTask($task['taskUid']);

        $task = $index->resetFacetSearch();
        $index->waitForTask($task['taskUid']);

        self::assertTrue($index->getFacetSearch());
    }
}
