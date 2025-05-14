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

        $task = $this->index->updateSynonyms($newSynonyms);
        $this->index->waitForTask($task['taskUid']);

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testUpdateSynonymsWithEmptyArray(): void
    {
        $newSynonyms = [];

        $task = $this->index->updateSynonyms($newSynonyms);
        $this->index->waitForTask($task['taskUid']);

        self::assertSame($newSynonyms, $this->index->getSynonyms());
    }

    public function testResetSynonyms(): void
    {
        $task = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForTask($task['taskUid']);
        $task = $this->index->resetSynonyms();

        $this->index->waitForTask($task['taskUid']);

        self::assertEmpty($this->index->getSynonyms());
    }
}
