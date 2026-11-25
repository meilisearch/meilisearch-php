<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class WordPositionTest extends TestCase
{
    public function testGetDefaultWordPosition(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        self::assertSame(['enabled' => true], $index->getWordPosition());
    }

    public function testUpdateWordPosition(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateWordPosition(['enabled' => false])->wait();

        self::assertSame(['enabled' => false], $index->getWordPosition());
    }

    public function testUpdateWordPositionWithNullResetsToDefault(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateWordPosition(['enabled' => false])->wait();
        $index->updateWordPosition(['enabled' => null])->wait();

        self::assertSame(['enabled' => true], $index->getWordPosition());
    }

    public function testResetWordPosition(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateWordPosition(['enabled' => false])->wait();
        $index->resetWordPosition()->wait();

        self::assertSame(['enabled' => true], $index->getWordPosition());
    }
}
