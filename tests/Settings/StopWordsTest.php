<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class StopWordsTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultStopWords(): void
    {
        $response = $this->index->getStopWords();

        self::assertEmpty($response);
    }

    public function testUpdateStopWords(): void
    {
        $newStopWords = ['the'];
        $this->index->updateStopWords($newStopWords)->wait();

        self::assertSame($newStopWords, $this->index->getStopWords());
    }

    public function testResetStopWords(): void
    {
        $this->index->updateStopWords(['the'])->wait();
        $this->index->resetStopWords()->wait();

        self::assertEmpty($this->index->getStopWords());
    }
}
