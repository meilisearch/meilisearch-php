<?php

use MeiliSearch\Client;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    private $client;

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
        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->deleteAllIndexes();
    }

    public function testGetDefaultSettings()
    {
        $primaryKey = 'ObjectID';
        $settingA = $this->client
            ->createIndex('indexA')
            ->getSettings();
        $settingB = $this->client
            ->createIndex([
                'uid' => 'indexB',
                'primaryKey' => $primaryKey,
            ])->getSettings();

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settingA['rankingRules']);
        $this->assertNull($settingA['distinctAttribute']);
        $this->assertIsArray($settingA['searchableAttributes']);
        $this->assertEmpty($settingA['searchableAttributes']);
        $this->assertIsArray($settingA['displayedAttributes']);
        $this->assertEmpty($settingA['displayedAttributes']);
        $this->assertIsArray($settingA['stopWords']);
        $this->assertEmpty($settingA['stopWords']);
        $this->assertIsArray($settingA['synonyms']);
        $this->assertEmpty($settingA['synonyms']);
        $this->assertTrue($settingA['acceptNewFields']);

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        $this->assertNull($settingB['distinctAttribute']);
        $this->assertEquals([$primaryKey], $settingB['searchableAttributes']);
        $this->assertEquals([$primaryKey], $settingB['displayedAttributes']);
        $this->assertIsArray($settingB['stopWords']);
        $this->assertEmpty($settingB['stopWords']);
        $this->assertIsArray($settingB['synonyms']);
        $this->assertEmpty($settingB['synonyms']);
        $this->assertTrue($settingB['acceptNewFields']);
    }

    public function testUpdateSettings()
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

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
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->updateSettings([
            'searchableAttributes' => ['title'],
        ]);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

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
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetSettings();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settings['rankingRules']);
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
