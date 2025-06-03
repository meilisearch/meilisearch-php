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
    public const DEFAULT_FACET_SEARCH = true;
    public const DEFAULT_PREFIX_SEARCH = 'indexingTime';

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

        self::assertSame(self::DEFAULT_RANKING_RULES, $settingA['rankingRules']);
        self::assertNull($settingA['distinctAttribute']);
        self::assertIsArray($settingA['searchableAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingA['searchableAttributes']);
        self::assertIsArray($settingA['displayedAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingA['displayedAttributes']);
        self::assertIsArray($settingA['stopWords']);
        self::assertEmpty($settingA['stopWords']);
        self::assertIsIterable($settingA['synonyms']);
        self::assertEmpty($settingA['synonyms']);
        self::assertIsArray($settingA['filterableAttributes']);
        self::assertEmpty($settingA['filterableAttributes']);
        self::assertIsArray($settingA['sortableAttributes']);
        self::assertEmpty($settingA['sortableAttributes']);
        self::assertIsIterable($settingA['typoTolerance']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingA['typoTolerance']));
        self::assertSame(self::DEFAULT_FACET_SEARCH, $settingA['facetSearch']);
        self::assertSame(self::DEFAULT_PREFIX_SEARCH, $settingA['prefixSearch']);
        self::assertSame(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        self::assertNull($settingB['distinctAttribute']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingB['searchableAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingB['displayedAttributes']);
        self::assertIsArray($settingB['stopWords']);
        self::assertEmpty($settingB['stopWords']);
        self::assertIsIterable($settingB['synonyms']);
        self::assertEmpty($settingB['synonyms']);
        self::assertIsArray($settingB['filterableAttributes']);
        self::assertEmpty($settingB['filterableAttributes']);
        self::assertIsArray($settingB['sortableAttributes']);
        self::assertEmpty($settingB['sortableAttributes']);
        self::assertIsIterable($settingB['typoTolerance']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settingB['typoTolerance']));
        self::assertSame(self::DEFAULT_FACET_SEARCH, $settingB['facetSearch']);
        self::assertSame(self::DEFAULT_PREFIX_SEARCH, $settingB['prefixSearch']);
    }

    public function testUpdateSettings(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
            'facetSearch' => false,
            'prefixSearch' => 'disabled',
        ])->wait();

        $settings = $index->getSettings();

        self::assertSame(['title:asc', 'typo'], $settings['rankingRules']);
        self::assertSame('title', $settings['distinctAttribute']);
        self::assertIsArray($settings['searchableAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertSame(['the'], $settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
        self::assertFalse($settings['facetSearch']);
        self::assertSame('disabled', $settings['prefixSearch']);
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

        $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
            'typoTolerance' => $new_typo_tolerance,
        ])->wait();

        $index->updateSettings([
            'searchableAttributes' => ['title'],
        ])->wait();

        $settings = $index->getSettings();

        self::assertSame(['title:asc', 'typo'], $settings['rankingRules']);
        self::assertSame('title', $settings['distinctAttribute']);
        self::assertSame(['title'], $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertSame(['the'], $settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame($new_typo_tolerance, iterator_to_array($settings['typoTolerance']));
    }

    public function testResetSettings(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $index->updateSettings([
            'distinctAttribute' => 'title',
            'rankingRules' => ['title:asc', 'typo'],
            'stopWords' => ['the'],
        ])->wait();

        $index->resetSettings()->wait();

        $settings = $index->getSettings();

        self::assertSame(self::DEFAULT_RANKING_RULES, $settings['rankingRules']);
        self::assertNull($settings['distinctAttribute']);
        self::assertIsArray($settings['searchableAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertIsArray($settings['displayedAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertIsArray($settings['stopWords']);
        self::assertEmpty($settings['stopWords']);
        self::assertIsIterable($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertIsArray($settings['sortableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, iterator_to_array($settings['typoTolerance']));
        self::assertSame(self::DEFAULT_FACET_SEARCH, $settings['facetSearch']);
        self::assertSame(self::DEFAULT_PREFIX_SEARCH, $settings['prefixSearch']);
    }
}
