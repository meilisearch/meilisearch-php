<?php

declare(strict_types=1);

namespace Tests\Settings;

use MeiliSearch\Endpoints\Indexes;
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

        $this->assertEmpty($response);
    }

    public function testUpdateStopWords(): void
    {
        $newStopWords = ['the'];
        $promise = $this->index->updateStopWords($newStopWords);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['taskUid']);
        $stopWords = $this->index->getStopWords();

        $this->assertEquals($newStopWords, $stopWords);
    }

    public function testResetStopWords(): void
    {
        $promise = $this->index->updateStopWords(['the']);
        $this->index->waitForTask($promise['taskUid']);

        $promise = $this->index->resetStopWords();

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);

        $stopWords = $this->index->getStopWords();

        $this->assertEmpty($stopWords);
    }
}
