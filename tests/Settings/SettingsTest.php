<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SettingsTest extends TestCase
{
    public const DEFAULT_RANKING_RULES = [
        'words',
        'typo',
        'proximity',
        'attribute',
        'sort',
        'exactness',
    ];

    public const DEFAULT_SEARCHABLE_ATTRIBUTES = ['*'];
    public const DEFAULT_DISPLAYED_ATTRIBUTES = ['*'];

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
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingA['searchableAttributes']);
        $this->assertIsArray($settingA['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingA['displayedAttributes']);
        $this->assertIsArray($settingA['stopWords']);
        $this->assertEmpty($settingA['stopWords']);
        $this->assertIsArray($settingA['synonyms']);
        $this->assertEmpty($settingA['synonyms']);
        $this->assertIsArray($settingA['filterableAttributes']);
        $this->assertEmpty($settingA['filterableAttributes']);
        $this->assertIsArray($settingA['sortableAttributes']);
        $this->assertEmpty($settingA['sortableAttributes']);

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        $this->assertNull($settingB['distinctAttribute']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingB['searchableAttributes']);
        $this->assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingB['displayedAttributes']);
        $this->assertIsArray($settingB['stopWords']);
        $this->assertEmpty($settingB['stopWords']);
        $this->assertIsArray($settingB['synonyms']);
        $this->assertEmpty($settingB['synonyms']);
        $this->assertIsArray($settingB['filterableAttributes']);
        $this->assertEmpty($settingB['filterableAttributes']);
        $this->assertIsArray($settingB['sortableAttributes']);
        $this->assertEmpty($settingB['sortableAttributes']);
    }

    public function testUpdateSettings(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $settings = $index->getSettings();

        $this->assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
    }

    public function testUpdateSettingsWithoutOverwritingThem(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
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

        $this->assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertEquals(['title'], $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
    }

    public function testResetSettings(): void
    {
        $index = $this->client->createIndex('index');
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
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
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertIsArray($settings['stopWords']);
        $this->assertEmpty($settings['stopWords']);
        $this->assertIsArray($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
    }
}
