<?php

use MeiliSearch\Client;
use Tests\TestCase;

class RankingRulesTest extends TestCase
{
    private $index;

    const DEFAULT_RANKING_RULES = [
        'typo',
        'words',
        'proximity',
        'attribute',
        'wordsPosition',
        'exactness',
    ];

    public function __construct()
    {
        parent::__construct();

        $client = new Client('http://localhost:7700', 'masterKey');
        $client->deleteAllIndexes();
        $this->index = $client->createIndex('index');
    }

    public function testGetDefaultRankingRules()
    {
        $response = $this->index->getRankingRules();

        $this->assertIsArray($response);
        $this->assertEquals(self::DEFAULT_RANKING_RULES, $response);
    }

    public function testUpdateRankingRules()
    {
        $newRankingRules = [
            'asc(title)',
            'typo',
            'desc(description)',
        ];

        $promise = $this->index->updateRankingRules($newRankingRules);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $rankingRules = $this->index->getRankingRules();

        $this->assertIsArray($rankingRules);
        $this->assertEquals($newRankingRules, $rankingRules);
    }

    public function testResetRankingRules()
    {
        $promise = $this->index->resetRankingRules();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);

        $this->index->waitForPendingUpdate($promise['updateId']);
        $rankingRules = $this->index->getRankingRules();

        $this->assertIsArray($rankingRules);
        $this->assertEquals(self::DEFAULT_RANKING_RULES, $rankingRules);
    }
}
