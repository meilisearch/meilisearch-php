<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class PrefixSearchTest extends TestCase
{
    public function testGetDefaultPrefixSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $prefixSearch = $index->getPrefixSearch();

        self::assertSame('indexingTime', $prefixSearch);
    }

    public function testUpdatePrefixSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updatePrefixSearch('disabled');
        $index->waitForTask($task->getTaskUid());

        self::assertSame('disabled', $index->getPrefixSearch());
    }

    public function testResetPrefixSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updatePrefixSearch('disabled');
        $index->waitForTask($task->getTaskUid());

        $task = $index->resetPrefixSearch();
        $index->waitForTask($task->getTaskUid());

        self::assertSame('indexingTime', $index->getPrefixSearch());
    }
}
