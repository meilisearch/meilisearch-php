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

        $promise = $index->updatePrefixSearch('disabled');

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $prefixSearch = $index->getPrefixSearch();
        self::assertSame('disabled', $prefixSearch);
    }

    public function testResetPrefixSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updatePrefixSearch('disabled');
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetPrefixSearch();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $prefixSearch = $index->getPrefixSearch();
        self::assertSame('indexingTime', $prefixSearch);
    }
}
