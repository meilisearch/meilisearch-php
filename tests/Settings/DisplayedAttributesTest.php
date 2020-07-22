<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class DisplayedAttributesTest extends TestCase
{
    public function testGetDefaultDisplayedAttributes(): void
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex('indexB', ['primaryKey' => 'objectID']);

        $attributesA = $indexA->getDisplayedAttributes();
        $attributesB = $indexB->getDisplayedAttributes();

        $this->assertIsArray($attributesA);
        $this->assertEquals(['*'], $attributesA);

        $this->assertIsArray($attributesB);
        $this->assertEquals(['*'], $attributesB);
    }

    public function testUpdateDisplayedAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->client->createIndex('index');

        $promise = $index->updateDisplayedAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $displayedAttributes = $index->getDisplayedAttributes();

        $this->assertIsArray($displayedAttributes);
        $this->assertEquals($newAttributes, $displayedAttributes);
    }

    public function testResetDisplayedAttributes(): void
    {
        $index = $this->client->createIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateDisplayedAttributes($newAttributes);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetDisplayedAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $displayedAttributes = $index->getDisplayedAttributes();
        $this->assertIsArray($displayedAttributes);
        $this->assertEquals(['*'], $displayedAttributes);
    }
}
