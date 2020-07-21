<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class AttributesForFacetingTest extends TestCase
{
    public function testGetDefaultAttributesForFaceting(): void
    {
        $index = $this->client->createIndex('index');

        $attributes = $index->getAttributesForFaceting();

        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testUpdateAttributesForFaceting(): void
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

    public function testResetAttributesForFaceting(): void
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
