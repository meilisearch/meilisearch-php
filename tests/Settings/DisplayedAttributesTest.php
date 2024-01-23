<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class DisplayedAttributesTest extends TestCase
{
    public function testGetDefaultDisplayedAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $indexB = $this->createEmptyIndex($this->safeIndexName('books-2'), ['primaryKey' => 'objectID']);

        $attributesA = $indexA->getDisplayedAttributes();
        $attributesB = $indexB->getDisplayedAttributes();

        self::assertSame(['*'], $attributesA);
        self::assertSame(['*'], $attributesB);
    }

    public function testUpdateDisplayedAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateDisplayedAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $displayedAttributes = $index->getDisplayedAttributes();

        self::assertSame($newAttributes, $displayedAttributes);
    }

    public function testResetDisplayedAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $promise = $index->updateDisplayedAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetDisplayedAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $displayedAttributes = $index->getDisplayedAttributes();
        self::assertSame(['*'], $displayedAttributes);
    }
}
