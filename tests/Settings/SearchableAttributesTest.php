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

        self::assertEquals(['*'], $searchableAttributesA);
        self::assertEquals(['*'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $searchableAttributes = [
            'title',
            'description',
        ];

        $promise = $indexA->updateSearchableAttributes($searchableAttributes);

        $this->assertIsValidPromise($promise);

        $indexA->waitForTask($promise['taskUid']);
        $updatedAttributes = $indexA->getSearchableAttributes();

        self::assertEquals($searchableAttributes, $updatedAttributes);
    }

    public function testResetSearchableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $promise = $index->resetSearchableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $searchableAttributes = $index->getSearchableAttributes();

        self::assertEquals(['*'], $searchableAttributes);
    }
}
