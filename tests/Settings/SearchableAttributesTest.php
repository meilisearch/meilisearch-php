<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SearchableAttributesTest extends TestCase
{
    public function testGetDefaultSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $indexB = $this->createEmptyIndex($this->safeIndexName('books-2'), ['primaryKey' => 'objectID']);

        $searchableAttributesA = $indexA->getSearchableAttributes();
        $searchableAttributesB = $indexB->getSearchableAttributes();

        self::assertSame(['*'], $searchableAttributesA);
        self::assertSame(['*'], $searchableAttributesB);
    }

    public function testUpdateSearchableAttributes(): void
    {
        $indexA = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $searchableAttributes = [
            'title',
            'description',
        ];

        $task = $indexA->updateSearchableAttributes($searchableAttributes);
        $indexA->waitForTask($task->getTaskUid());

        self::assertSame($searchableAttributes, $indexA->getSearchableAttributes());
    }

    public function testResetSearchableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));

        $task = $index->resetSearchableAttributes();
        $index->waitForTask($task->getTaskUid());

        self::assertSame(['*'], $index->getSearchableAttributes());
    }
}
