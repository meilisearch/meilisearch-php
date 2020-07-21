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
        $this->index = $this->client->createIndex('index');
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

        $this->index->waitForPendingUpdate($promise['updateId']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testResetSynonyms(): void
    {
        $promise = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $promise = $this->index->resetSynonyms();

        $this->assertIsValidPromise($promise);

        $this->index->waitForPendingUpdate($promise['updateId']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEmpty($synonyms);
    }
}
