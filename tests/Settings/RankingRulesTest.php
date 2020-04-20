<?php

use MeiliSearch\Client;
use Tests\TestCase;

class RankingRulesTest extends TestCase
{
    private static $client;
    private static $index;
    private static $default_ranking_rules;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes(static::$client);
        static::$index = static::$client->createIndex('uid');
        static::$default_ranking_rules = [
            'typo',
            'words',
            'proximity',
            'attribute',
            'wordsPosition',
            'exactness',
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testGetDefaultRankingRules()
    {
        $res = static::$index->getRankingRules();
        $this->assertIsArray($res);
        $this->assertEquals(static::$default_ranking_rules, $res);
    }

    public function testUpdateRankingRules()
    {
        $new_rr = [
            'asc(title)',
            'typo',
            'desc(description)',
        ];
        $res = static::$index->updateRankingRules($new_rr);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForPendingUpdate($res['updateId']);
        $rr = static::$index->getRankingRules();
        $this->assertIsArray($rr);
        $this->assertEquals($new_rr, $rr);
    }

    public function testResetRankingRules()
    {
        $res = static::$index->resetRankingRules();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForPendingUpdate($res['updateId']);
        $rr = static::$index->getRankingRules();
        $this->assertIsArray($rr);
        $this->assertEquals(static::$default_ranking_rules, $rr);
    }
}
