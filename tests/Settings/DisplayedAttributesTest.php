<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class DisplayedAttributesTest extends TestCase
{
    public function testGetDefaultDisplayedAttributes(): void
    {
        $indexA = $this->createEmptyIndex('indexA');
        $indexB = $this->createEmptyIndex('indexB', ['primaryKey' => 'objectID']);

        $attributesA = $indexA->getDisplayedAttributes();
        $attributesB = $indexB->getDisplayedAttributes();

        $this->assertEquals(['*'], $attributesA);
        $this->assertEquals(['*'], $attributesB);
    }

    public function testUpdateDisplayedAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex('index');

        $promise = $index->updateDisplayedAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $displayedAttributes = $index->getDisplayedAttributes();

        $this->assertEquals($newAttributes, $displayedAttributes);
    }

    public function testResetDisplayedAttributes(): void
    {
        $index = $this->createEmptyIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateDisplayedAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetDisplayedAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $displayedAttributes = $index->getDisplayedAttributes();
        $this->assertEquals(['*'], $displayedAttributes);
    }
}
