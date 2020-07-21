<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SettingsTest extends TestCase
{
    const DEFAULT_RANKING_RULES = [
        'typo',
        'words',
        'proximity',
        'attribute',
        'wordsPosition',
        'exactness',
    ];

    public function testGetDefaultSettings(): void
    {
        $primaryKey = 'ObjectID';
        $settingA = $this->client
            ->createIndex('indexA')
            ->getSettings();
        $settingB = $this->client
            ->createIndex(
                'indexB',
                ['primaryKey' => $primaryKey]
            )->getSettings();

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

    public function testUpdateSettings(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

        $this->assertEquals(['asc(title)', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertEmpty($settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEmpty($settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertTrue($settings['acceptNewFields']);
    }

    public function testUpdateSettingsWithoutOverwritingThem(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->updateSettings([
            'searchableAttributes' => ['title'],
        ]);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

        $this->assertEquals(['asc(title)', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertEquals(['title'], $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEmpty($settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertTrue($settings['acceptNewFields']);
    }

    public function testResetSettings(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['asc(title)', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetSettings();

        $this->assertIsValidPromise($promise);
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
