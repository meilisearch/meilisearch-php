<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class LocalizedAttributesTest extends TestCase
{
    public function testGetDefaultLocalizedAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $indexB = $this->createEmptyIndex($this->safeIndexName('books-2'), ['primaryKey' => 'objectID']);

        $attributesA = $indexA->getLocalizedAttributes();
        $attributesB = $indexB->getLocalizedAttributes();

        self::assertNull($attributesA);
        self::assertNull($attributesB);
    }

    public function testUpdateLocalizedAttributes(): void
    {
        $newAttributes = [['attributePatterns' => ['doggo'], 'locales' => ['fra']]];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateLocalizedAttributes($newAttributes);

        $index->waitForTask($promise['taskUid']);

        $localizedAttributes = $index->getLocalizedAttributes();

        self::assertSame($newAttributes, $localizedAttributes);
    }

    public function testResetLocalizedAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = [['attributePatterns' => ['doggo'], 'locales' => ['fra']]];

        $promise = $index->updateLocalizedAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetLocalizedAttributes();
        $index->waitForTask($promise['taskUid']);

        $localizedAttributes = $index->getLocalizedAttributes();
        self::assertNull($localizedAttributes);
    }
}
