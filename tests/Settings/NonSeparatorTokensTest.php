<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class NonSeparatorTokensTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_NON_SEPARATOR_TOKENS = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultNonSeparatorTokens(): void
    {
        $response = $this->index->getNonSeparatorTokens();

        self::assertSame(self::DEFAULT_NON_SEPARATOR_TOKENS, $response);
    }

    public function testUpdateNonSeparatorTokens(): void
    {
        $newNonSeparatorTokens = [
            '&sep',
            '/',
            '|',
        ];

        $task = $this->index->updateNonSeparatorTokens($newNonSeparatorTokens);
        $this->index->waitForTask($task['taskUid']);

        self::assertSame($newNonSeparatorTokens, $this->index->getNonSeparatorTokens());
    }

    public function testResetNonSeparatorTokens(): void
    {
        $task = $this->index->resetNonSeparatorTokens();
        $this->index->waitForTask($task['taskUid']);

        self::assertSame(self::DEFAULT_NON_SEPARATOR_TOKENS, $this->index->getNonSeparatorTokens());
    }
}
