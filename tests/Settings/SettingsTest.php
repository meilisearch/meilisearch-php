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

        self::assertEquals(self::DEFAULT_RANKING_RULES, $settingA['rankingRules']);
        self::assertNull($settingA['distinctAttribute']);
        self::assertIsArray($settingA['searchableAttributes']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingA['searchableAttributes']);
        self::assertIsArray($settingA['displayedAttributes']);
        self::assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingA['displayedAttributes']);
        self::assertIsArray($settingA['stopWords']);
        self::assertEmpty($settingA['stopWords']);
        self::assertIsIterable($settingA['synonyms']);
        self::assertEmpty($settingA['synonyms']);
        self::assertIsArray($settingA['filterableAttributes']);
        self::assertEmpty($settingA['filterableAttributes']);
        self::assertIsArray($settingA['sortableAttributes']);
        self::assertEmpty($settingA['sortableAttributes']);
        self::assertIsIterable($settingA['typoTolerance']);
        self::assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingA['typoTolerance']));

        self::assertEquals(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        self::assertNull($settingB['distinctAttribute']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingB['searchableAttributes']);
        self::assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingB['displayedAttributes']);
        self::assertIsArray($settingB['stopWords']);
        self::assertEmpty($settingB['stopWords']);
        self::assertIsIterable($settingB['synonyms']);
        self::assertEmpty($settingB['synonyms']);
        self::assertIsArray($settingB['filterableAttributes']);
        self::assertEmpty($settingB['filterableAttributes']);
        self::assertIsArray($settingB['sortableAttributes']);
        self::assertEmpty($settingB['sortableAttributes']);
        self::assertIsIterable($settingB['typoTolerance']);
        self::assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingB['typoTolerance']));
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

        self::assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        self::assertEquals('title', $settings['distinctAttribute']);
        self::assertIsArray($settings['searchableAttributes']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertEquals(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertEquals(['the'], $settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
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

        self::assertEquals(['title:asc', 'typo'], $settings['rankingRules']);
        self::assertEquals('title', $settings['distinctAttribute']);
        self::assertEquals(['title'], $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertEquals(['the'], $settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertEquals($new_typo_tolerance, iterator_to_array($settings['typoTolerance']));
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

        self::assertEquals(self::DEFAULT_RANKING_RULES, $settings['rankingRules']);
        self::assertNull($settings['distinctAttribute']);
        self::assertIsArray($settings['searchableAttributes']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertEquals(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertIsArray($settings['stopWords']);
        self::assertEmpty($settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertEquals(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
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
