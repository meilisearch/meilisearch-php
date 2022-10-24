<?php

declare(strict_types=1);

namespace Tests\Settings;

use MeiliSearch\Endpoints\Indexes;
use Tests\TestCase;

final class SynonymsTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultSynonyms(): void
    {
        $this->assertEmpty($this->index->getSynonyms());
    }

    public function testUpdateSynonyms(): void
    {
        $newSynonyms = [
            'hp' => ['harry potter'],
        ];
        $promise = $this->index->updateSynonyms($newSynonyms);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['taskUid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testUpdateSynonymsWithEmptyArray(): void
    {
        $newSynonyms = [];
        $promise = $this->index->updateSynonyms($newSynonyms);

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['taskUid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testResetSynonyms(): void
    {
        $promise = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForTask($promise['taskUid']);
        $promise = $this->index->resetSynonyms();

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['taskUid']);
        $synonyms = $this->index->getSynonyms();

        $this->assertEmpty($synonyms);
    }
}
