<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class WordDictionaryTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_WORD_DICTIONARY = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultWordDictionary(): void
    {
        self::assertSame(self::DEFAULT_WORD_DICTIONARY, $this->index->getDictionary());
    }

    public function testUpdateWordDictionary(): void
    {
        $newWordDictionary = [
            'J. K.',
            'J. R. R.',
        ];

        $task = $this->index->updateDictionary($newWordDictionary);
        $this->index->waitForTask($task['taskUid']);

        self::assertSame($newWordDictionary, $this->index->getDictionary());
    }

    public function testResetWordDictionary(): void
    {
        $task = $this->index->resetDictionary();
        $this->index->waitForTask($task['taskUid']);

        self::assertSame(self::DEFAULT_WORD_DICTIONARY, $this->index->getDictionary());
    }
}
