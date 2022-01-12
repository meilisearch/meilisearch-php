<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SynonymsTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex('index');
    }

    public function testGetDefaultSynonyms(): void
    {
        $response = $this->index->getSynonyms();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testUpdateSynonyms(): void
    {
        $newSynonyms = [
            'hp' => ['harry potter'],
        ];
        $promise = $this->index->updateSynonyms($newSynonyms);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['uid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testUpdateSynonymsWithEmptyArray(): void
    {
        $newSynonyms = [];
        $promise = $this->index->updateSynonyms($newSynonyms);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['uid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testResetSynonyms(): void
    {
        $promise = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForTask($promise['uid']);
        $promise = $this->index->resetSynonyms();

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['uid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEmpty($synonyms);
    }
}
