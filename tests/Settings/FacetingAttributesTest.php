<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FacetingAttributesTest extends TestCase
{
    public function testGetDefaultFaceting(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => [
                '*' => 'alpha',
            ],
        ], $index->getFaceting()->toArray());
    }

    public function testUpdateFacetingAttributes(): void
    {
        $newAttributes = ['sortFacetValuesBy' => ['*' => 'count']];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFaceting($newAttributes)->wait();

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'count'],
        ], $index->getFaceting()->toArray());
    }

    public function testUpdateFacetingAttributesWithSampledCountForAllFacets(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFaceting(['sortFacetValuesBy' => ['*' => 'sampledCount']])->wait();

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'sampledCount'],
        ], $index->getFaceting()->toArray());
    }

    public function testUpdateFacetingAttributesWithSampledCountForAnAttribute(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFaceting([
            'maxValuesPerFacet' => 2,
            'sortFacetValuesBy' => ['*' => 'alpha', 'genres' => 'sampledCount'],
        ])->wait();

        self::assertSame([
            'maxValuesPerFacet' => 2,
            'sortFacetValuesBy' => ['*' => 'alpha', 'genres' => 'sampledCount'],
        ], $index->getFaceting()->toArray());
    }

    public function testResetFacetingAfterSampledCount(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFaceting(['sortFacetValuesBy' => ['*' => 'sampledCount']])->wait();
        $index->resetFaceting()->wait();

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'alpha'],
        ], $index->getFaceting()->toArray());
    }

    public function testResetFaceting(): void
    {
        $newAttributes = ['sortFacetValuesBy' => ['*' => 'count']];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateFaceting($newAttributes)->wait();
        $index->resetFaceting()->wait();

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'alpha'],
        ], $index->getFaceting()->toArray());
    }
}
