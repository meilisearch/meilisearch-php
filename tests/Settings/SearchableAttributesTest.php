<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SearchableAttributesTest extends TestCase
{
    public function testGetDefaultSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $indexB = $this->createEmptyIndex($this->safeIndexName('books-2'), ['primaryKey' => 'objectID']);

        $searchableAttributesA = $indexA->getSearchableAttributes();
        $searchableAttributesB = $indexB->getSearchableAttributes();

        self::assertSame(['*'], $searchableAttributesA);
        self::assertSame(['*'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $searchableAttributes = [
            'title',
            'description',
        ];

        $indexA->updateSearchableAttributes($searchableAttributes)->wait();

        self::assertSame($searchableAttributes, $indexA->getSearchableAttributes());
    }

    public function testResetSearchableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));

        $index->resetSearchableAttributes()->wait();

        self::assertSame(['*'], $index->getSearchableAttributes());
    }
}
