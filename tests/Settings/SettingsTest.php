<?php

use MeiliSearch\Client;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    private static $client;
    private static $index1;
    private static $index2;
    private static $primary_key;
    private static $default_ranking_rules;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$primary_key = 'objectID';
        static::$client = new Client('http://localhost:7700', 'masterKey');
        static::$client->deleteAllIndexes();
        static::$index1 = static::$client->createIndex('uid1');
        static::$index2 = static::$client->createIndex([
            'uid' => 'uid2',
            'primaryKey' => static::$primary_key,
        ]);
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
        static::$client->deleteAllIndexes();
    }

    public function testGetDefaultSettings()
    {
        $res = static::$index1->getSettings();
        $this->assertEquals(static::$default_ranking_rules, $res['rankingRules']);
        $this->assertNull($res['distinctAttribute']);
        $this->assertIsArray($res['searchableAttributes']);
        $this->assertEmpty($res['searchableAttributes']);
        $this->assertIsArray($res['displayedAttributes']);
        $this->assertEmpty($res['displayedAttributes']);
        $this->assertIsArray($res['stopWords']);
        $this->assertEmpty($res['stopWords']);
        $this->assertIsArray($res['synonyms']);
        $this->assertEmpty($res['synonyms']);
        $this->assertTrue($res['acceptNewFields']);
        $res = static::$index2->getSettings();
        $this->assertEquals(static::$default_ranking_rules, $res['rankingRules']);
        $this->assertNull($res['distinctAttribute']);
        $this->assertEquals([static::$primary_key], $res['searchableAttributes']);
        $this->assertEquals([static::$primary_key], $res['displayedAttributes']);
        $this->assertIsArray($res['stopWords']);
        $this->assertEmpty($res['stopWords']);
        $this->assertIsArray($res['synonyms']);
        $this->assertEmpty($res['synonyms']);
        $this->assertTrue($res['acceptNewFields']);
    }

    public function testUpdateSettings()
    {
        $res = static::$index1->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $settings = static::$index1->getSettings();
        $this->assertEquals(['asc(title)', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertEquals(['title'], $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(['title'], $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertTrue($settings['acceptNewFields']);
    }

    public function testUpdateSettingsWithoutOverwritingThem()
    {
        $res = static::$index1->updateSettings([
            'searchableAttributes' => ['title'],
        ]);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $settings = static::$index1->getSettings();
        $this->assertEquals(['asc(title)', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertEquals(['title'], $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(['title'], $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertTrue($settings['acceptNewFields']);
    }

    public function testResetSettings()
    {
        $res = static::$index1->resetSettings();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $settings = static::$index1->getSettings();
        $this->assertEquals(static::$default_ranking_rules, $settings['rankingRules']);
        $this->assertNull($settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertIsArray($settings['stopWords']);
        $this->assertEmpty($settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertTrue($settings['acceptNewFields']);
    }
}
