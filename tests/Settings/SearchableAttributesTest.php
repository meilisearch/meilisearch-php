<?php

namespace Tests\Settings;

use Tests\TestCase;

class SearchableAttributesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetDefaultSearchableAttributes()
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB', ['primaryKey' => 'objectID']);

        $searchableAttributesA = $indexA->getSearchableAttributes();
        $searchableAttributesB = $indexB->getSearchableAttributes();

        $this->assertIsArray($searchableAttributesA);
        $this->assertEmpty($searchableAttributesA);
        $this->assertIsArray($searchableAttributesB);
        $this->assertEquals(['objectID'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes()
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

    public function testResetSearchableAttributes()
    {
        $index = $this->client->createIndex('indexA');
        $promise = $index->resetSearchableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $searchableAttributes = $index->getSearchableAttributes();

        $this->assertIsArray($searchableAttributes);
    }
}
