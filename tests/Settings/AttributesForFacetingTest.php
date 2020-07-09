<?php

namespace Tests\Settings;

use Tests\TestCase;

class AttributesForFacetingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetDefaultAttributesForFaceting()
    {
        $index = $this->client->createIndex('index');

        $attributes = $index->getAttributesForFaceting();

        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testUpdateAttributesForFaceting()
    {
        $newAttributes = ['title'];
        $index = $this->client->createIndex('index');

        $promise = $index->updateAttributesForFaceting($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $attributesForFaceting = $index->getAttributesForFaceting();

        $this->assertIsArray($attributesForFaceting);
        $this->assertEquals($newAttributes, $attributesForFaceting);
    }

    public function testResetAttributesForFaceting()
    {
        $index = $this->client->createIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateAttributesForFaceting($newAttributes);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetAttributesForFaceting();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $attributesForFaceting = $index->getAttributesForFaceting();
        $this->assertIsArray($attributesForFaceting);
        $this->assertEmpty($attributesForFaceting);
    }
}
