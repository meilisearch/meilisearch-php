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

    public function testGetDefaultSynonyms()
    {
        $response = $this->index->getSynonyms();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testUpdateSynonyms()
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

    public function testResetSynonyms()
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
