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

    public const DEFAULT_TYPO_TOLERANCE = [
        'enabled' => true,
        'minWordSizeForTypos' => [
            'oneTypo' => 5,
            'twoTypos' => 9,
        ],
        'disableOnWords' => [],
        'disableOnAttributes' => [],
    ];

    public const DEFAULT_SEARCHABLE_ATTRIBUTES = ['*'];
    public const DEFAULT_DISPLAYED_ATTRIBUTES = ['*'];

    public function testGetDefaultSettings(): void
    {
        $primaryKey = 'ObjectID';
        $settingA = $this
            ->createEmptyIndex($this->safeIndexName('books-1'))
            ->getSettings();
        $settingB = $this
            ->createEmptyIndex(
                $this->safeIndexName('books-1'),
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
        $this->assertIsIterable($settingA['synonyms']);
        $this->assertEmpty($settingA['synonyms']);
        $this->assertIsArray($settingA['filterableAttributes']);
        $this->assertEmpty($settingA['filterableAttributes']);
        $this->assertIsArray($settingA['sortableAttributes']);
        $this->assertEmpty($settingA['sortableAttributes']);
        $this->assertIsIterable($settingA['typoTolerance']);
        $this->assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingA['typoTolerance']));

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        $this->assertNull($settingB['distinctAttribute']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingB['searchableAttributes']);
        $this->assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingB['displayedAttributes']);
        $this->assertIsArray($settingB['stopWords']);
        $this->assertEmpty($settingB['stopWords']);
        $this->assertIsIterable($settingB['synonyms']);
        $this->assertEmpty($settingB['synonyms']);
        $this->assertIsArray($settingB['filterableAttributes']);
        $this->assertEmpty($settingB['filterableAttributes']);
        $this->assertIsArray($settingB['sortableAttributes']);
        $this->assertEmpty($settingB['sortableAttributes']);
        $this->assertIsIterable($settingB['typoTolerance']);
        $this->assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingB['typoTolerance']));
    }

    public function testUpdateSettings(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $settings = $index->getSettings();

        $this->assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsIterable($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
        $this->assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
    }

    public function testUpdateSettingsWithoutOverwritingThem(): void
    {
        $new_typo_tolerance = [
            'enabled' => true,
            'minWordSizeForTypos' => [
                'oneTypo' => 5,
                'twoTypos' => 9,
            ],
            'disableOnWords' => [],
            'disableOnAttributes' => [],
        ];

        $index = $this->createEmptyIndex($this->safeIndexName());
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
            'typoTolerance' => $new_typo_tolerance,
        ]);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->updateSettings([
            'searchableAttributes' => ['title'],
        ]);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $settings = $index->getSettings();

        $this->assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        $this->assertEquals('title', $settings['distinctAttribute']);
        $this->assertEquals(['title'], $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertEquals(['the'], $settings['stopWords']);
        $this->assertIsIterable($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
        $this->assertEquals($new_typo_tolerance, iterator_to_array($settings['typoTolerance']));
    }

    public function testResetSettings(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $promise = $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
        ]);
        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetSettings();

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $settings = $index->getSettings();

        $this->assertEquals(self::DEFAULT_RANKING_RULES, $settings['rankingRules']);
        $this->assertNull($settings['distinctAttribute']);
        $this->assertIsArray($settings['searchableAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        $this->assertIsArray($settings['displayedAttributes']);
        $this->assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        $this->assertIsArray($settings['stopWords']);
        $this->assertEmpty($settings['stopWords']);
        $this->assertIsIterable($settings['synonyms']);
        $this->assertEmpty($settings['synonyms']);
        $this->assertIsArray($settings['filterableAttributes']);
        $this->assertEmpty($settings['filterableAttributes']);
        $this->assertIsArray($settings['sortableAttributes']);
        $this->assertEmpty($settings['sortableAttributes']);
        $this->assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
    }

    // Here the test to prevent https://github.com/meilisearch/meilisearch-php/issues/204.
    // Rollback this comment after meilisearch v1.6.0 final release.
    // Related to: https://github.com/meilisearch/meilisearch/issues/4323
    //
    // public function testGetThenUpdateSettings(): void
    // {
    //     $http = new \Meilisearch\Http\Client($this->host, getenv('MEILISEARCH_API_KEY'));
    //     $http->patch('/experimental-features', ['vectorStore' => false]);
    //     $index = $this->createEmptyIndex($this->safeIndexName());

    //     $resetPromise = $index->resetSettings();
    //     $this->assertIsValidPromise($resetPromise);
    //     $index->waitForTask($resetPromise['taskUid']);

    //     $settings = $index->getSettings();
    //     $promise = $index->updateSettings($settings);
    //     $this->assertIsValidPromise($promise);
    //     $index->waitForTask($promise['taskUid']);
    // }
}
