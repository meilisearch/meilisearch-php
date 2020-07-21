<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SearchableAttributesTest extends TestCase
{
    public function testGetDefaultSearchableAttributes(): void
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB', ['primaryKey' => 'objectID']);

        $searchableAttributesA = $indexA->getSearchableAttributes();
        $searchableAttributesB = $indexB->getSearchableAttributes();

        $this->assertIsArray($searchableAttributesA);
        $this->assertEquals(['*'], $searchableAttributesA);
        $this->assertIsArray($searchableAttributesB);
        $this->assertEquals(['*'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes(): void
    {
        $indexA = $this->client->createIndex('indexA');
        $searchableAttributes = [
            'title',
            'description',
        ];

        $promise = $indexA->updateSearchableAttributes($searchableAttributes);

        $this->assertIsValidPromise($promise);

        $indexA->waitForPendingUpdate($promise['updateId']);
        $updatedAttributes = $indexA->getSearchableAttributes();

        $this->assertIsArray($updatedAttributes);
        $this->assertEquals($searchableAttributes, $updatedAttributes);
    }

    public function testResetSearchableAttributes(): void
    {
        $index = $this->client->createIndex('indexA');
        $promise = $index->resetSearchableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $searchableAttributes = $index->getSearchableAttributes();

        $this->assertIsArray($searchableAttributes);
        $this->assertEquals(['*'], $searchableAttributes);
    }
}
