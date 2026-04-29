<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Index;
use Tests\TestCase;

final class SynonymsTest extends TestCase
{
    private Index $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultSynonyms(): void
    {
        self::assertEmpty($this->index->getSynonyms());
    }

    public function testUpdateSynonyms(): void
    {
        $newSynonyms = [
            'hp' => ['harry potter'],
        ];

        $this->index->updateSynonyms($newSynonyms)->wait();

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testUpdateSynonymsWithEmptyArray(): void
    {
        $newSynonyms = [];

        $this->index->updateSynonyms($newSynonyms)->wait();

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testResetSynonyms(): void
    {
        $this->index->updateSynonyms(['hp' => ['harry potter']])->wait();
        $this->index->resetSynonyms()->wait();

        self::assertEmpty($this->index->getSynonyms());
    }
}
