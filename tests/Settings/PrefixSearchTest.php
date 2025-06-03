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
        $index->updatePrefixSearch('disabled')->wait();

        self::assertSame('disabled', $index->getPrefixSearch());
    }

    public function testResetPrefixSearch(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updatePrefixSearch('disabled')->wait();
        $index->resetPrefixSearch()->wait();

        self::assertSame('indexingTime', $index->getPrefixSearch());
    }
}
