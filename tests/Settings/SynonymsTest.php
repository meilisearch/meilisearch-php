<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
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
        self::assertEmpty($this->index->getSynonyms());
    }

    public function testUpdateSynonyms(): void
    {
        $newSynonyms = [
            'hp' => ['harry potter'],
        ];

        $promise = $this->index->updateSynonyms($newSynonyms);
        $this->index->waitForTask($promise['taskUid']);

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testUpdateSynonymsWithEmptyArray(): void
    {
        $newSynonyms = [];

        $promise = $this->index->updateSynonyms($newSynonyms);
        $this->index->waitForTask($promise['taskUid']);

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testResetSynonyms(): void
    {
        $promise = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForTask($promise['taskUid']);
        $promise = $this->index->resetSynonyms();

        $this->index->waitForTask($promise['taskUid']);

        self::assertEmpty($this->index->getSynonyms());
    }
}
