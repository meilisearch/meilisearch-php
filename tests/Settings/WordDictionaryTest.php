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
        $response = $this->index->getDictionary();

        self::assertSame(self::DEFAULT_WORD_DICTIONARY, $response);
    }

    public function testUpdateWordDictionary(): void
    {
        $newWordDictionary = [
            'J. K.',
            'J. R. R.',
        ];

        $promise = $this->index->updateDictionary($newWordDictionary);

        $this->index->waitForTask($promise['taskUid']);

        $wordDictionary = $this->index->getDictionary();

        self::assertSame($newWordDictionary, $wordDictionary);
    }

    public function testResetWordDictionary(): void
    {
        $promise = $this->index->resetDictionary();

        $this->index->waitForTask($promise['taskUid']);
        $wordDictionary = $this->index->getDictionary();

        self::assertSame(self::DEFAULT_WORD_DICTIONARY, $wordDictionary);
    }
}
