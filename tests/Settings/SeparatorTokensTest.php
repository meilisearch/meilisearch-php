<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class SeparatorTokensTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_SEPARATOR_TOKENS = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultSeparatorTokens(): void
    {
        $response = $this->index->getSeparatorTokens();

        $this->assertSame(self::DEFAULT_SEPARATOR_TOKENS, $response);
    }

    public function testUpdateSeparatorTokens(): void
    {
        $newSeparatorTokens = [
            '&sep',
            '/',
            '|',
        ];

        $promise = $this->index->updateSeparatorTokens($newSeparatorTokens);

        $this->index->waitForTask($promise['taskUid']);

        $separatorTokens = $this->index->getSeparatorTokens();

        $this->assertSame($newSeparatorTokens, $separatorTokens);
    }

    public function testResetSeparatorTokens(): void
    {
        $promise = $this->index->resetSeparatorTokens();

        $this->index->waitForTask($promise['taskUid']);
        $separatorTokens = $this->index->getSeparatorTokens();

        $this->assertSame(self::DEFAULT_SEPARATOR_TOKENS, $separatorTokens);
    }
}
