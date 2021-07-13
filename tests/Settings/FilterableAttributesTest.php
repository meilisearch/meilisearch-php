<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FilterableAttributesTest extends TestCase
{
    public function testGetDefaultFilterableAttributes(): void
    {
        $index = $this->client->createIndex('index');

        $attributes = $index->getFilterableAttributes();

        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testUpdateFilterableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->client->createIndex('index');

        $promise = $index->updateFilterableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $filterableAttributes = $index->getFilterableAttributes();

        $this->assertIsArray($filterableAttributes);
        $this->assertEquals($newAttributes, $filterableAttributes);
    }

    public function testResetFilterableAttributes(): void
    {
        $index = $this->client->createIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateFilterableAttributes($newAttributes);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetFilterableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $filterableAttributes = $index->getFilterableAttributes();
        $this->assertIsArray($filterableAttributes);
        $this->assertEmpty($filterableAttributes);
    }
}
