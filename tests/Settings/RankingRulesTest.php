<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class RankingRulesTest extends TestCase
{
    private $index;

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
        $this->index = $this->createEmptyIndex('index');
    }

    public function testGetDefaultRankingRules(): void
    {
        $response = $this->index->getRankingRules();

        $this->assertIsArray($response);
        $this->assertEquals(self::DEFAULT_RANKING_RULES, $response);
    }

    public function testUpdateRankingRules(): void
    {
        $newRankingRules = [
            'title:asc',
            'typo',
            'description:desc',
        ];

        $promise = $this->index->updateRankingRules($newRankingRules);

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['uid']);

        $rankingRules = $this->index->getRankingRules();

        $this->assertIsArray($rankingRules);
        $this->assertEquals($newRankingRules, $rankingRules);
    }

    public function testResetRankingRules(): void
    {
        $promise = $this->index->resetRankingRules();

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['uid']);
        $rankingRules = $this->index->getRankingRules();

        $this->assertIsArray($rankingRules);
        $this->assertEquals(self::DEFAULT_RANKING_RULES, $rankingRules);
    }
}
