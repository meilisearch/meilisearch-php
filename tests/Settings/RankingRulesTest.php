<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class RankingRulesTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_RANKING_RULES = [
        'words',
        'typo',
        'proximity',
        'attribute',
        'sort',
        'exactness',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultRankingRules(): void
    {
        $response = $this->index->getRankingRules();

        self::assertSame(self::DEFAULT_RANKING_RULES, $response);
    }

    public function testUpdateRankingRules(): void
    {
        $newRankingRules = [
            'title:asc',
            'typo',
            'description:desc',
        ];

        $task = $this->index->updateRankingRules($newRankingRules);

        $this->index->waitForTask($task->getTaskUid());

        $rankingRules = $this->index->getRankingRules();

        self::assertSame($newRankingRules, $rankingRules);
    }

    public function testResetRankingRules(): void
    {
        $task = $this->index->resetRankingRules();

        $this->index->waitForTask($task->getTaskUid());
        $rankingRules = $this->index->getRankingRules();

        self::assertSame(self::DEFAULT_RANKING_RULES, $rankingRules);
    }
}
