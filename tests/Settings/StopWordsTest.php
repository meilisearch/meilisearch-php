<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class StopWordsTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex('index');
    }

    public function testGetDefaultStopWords(): void
    {
        $response = $this->index->getStopWords();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testUpdateStopWords(): void
    {
        $newStopWords = ['the'];
        $promise = $this->index->updateStopWords($newStopWords);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['uid']);
        $stopWords = $this->index->getStopWords();

        $this->assertIsArray($stopWords);
        $this->assertEquals($newStopWords, $stopWords);
    }

    public function testResetStopWords(): void
    {
        $promise = $this->index->updateStopWords(['the']);
        $this->index->waitForTask($promise['uid']);

        $promise = $this->index->resetStopWords();

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['uid']);

        $topWords = $this->index->getStopWords();

        $this->assertIsArray($topWords);
        $this->assertEmpty($topWords);
    }
}
