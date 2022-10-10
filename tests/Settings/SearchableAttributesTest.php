<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SearchableAttributesTest extends TestCase
{
    public function testGetDefaultSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('indexA'));
        $indexB = $this->createEmptyIndex($this->safeIndexName('indexB'), ['primaryKey' => 'objectID']);

        $searchableAttributesA = $indexA->getSearchableAttributes();
        $searchableAttributesB = $indexB->getSearchableAttributes();

        $this->assertEquals(['*'], $searchableAttributesA);
        $this->assertEquals(['*'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('indexA'));
        $searchableAttributes = [
            'title',
            'description',
        ];

        $promise = $indexA->updateSearchableAttributes($searchableAttributes);

        $this->assertIsValidPromise($promise);

        $indexA->waitForTask($promise['taskUid']);
        $updatedAttributes = $indexA->getSearchableAttributes();

        $this->assertEquals($searchableAttributes, $updatedAttributes);
    }

    public function testResetSearchableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('indexA'));
        $promise = $index->resetSearchableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $searchableAttributes = $index->getSearchableAttributes();

        $this->assertEquals(['*'], $searchableAttributes);
    }
}
