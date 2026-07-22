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
        'attributeRank',
        'sort',
        'wordPosition',
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
        'disableOnNumbers' => false,
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
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingA['searchableAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingA['displayedAttributes']);
        self::assertEmpty($settingA['stopWords']);
        self::assertIsArray($settingA['synonyms']);
        self::assertEmpty($settingA['synonyms']);
        self::assertIsArray($settingA['filterableAttributes']);
        self::assertEmpty($settingA['filterableAttributes']);
        self::assertEmpty($settingA['sortableAttributes']);
        self::assertIsArray($settingA['typoTolerance']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $settingA['typoTolerance']);
        self::assertSame(self::DEFAULT_FACET_SEARCH, $settingA['facetSearch']);
        self::assertSame(self::DEFAULT_PREFIX_SEARCH, $settingA['prefixSearch']);
        self::assertSame(self::DEFAULT_RANKING_RULES, $settingB['rankingRules']);
        self::assertNull($settingB['distinctAttribute']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settingB['searchableAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settingB['displayedAttributes']);
        self::assertEmpty($settingB['stopWords']);
        self::assertIsArray($settingB['synonyms']);
        self::assertEmpty($settingB['synonyms']);
        self::assertIsArray($settingB['filterableAttributes']);
        self::assertEmpty($settingB['filterableAttributes']);
        self::assertEmpty($settingB['sortableAttributes']);
        self::assertIsArray($settingB['typoTolerance']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $settingB['typoTolerance']);
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
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertSame(self::DEFAULT_DISPLAYED_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertSame(['the'], $settings['stopWords']);
        self::assertIsArray($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $settings['typoTolerance']);
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
            'disableOnNumbers' => false,
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
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertSame(['the'], $settings['stopWords']);
        self::assertIsArray($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame($new_typo_tolerance, $settings['typoTolerance']);
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
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['searchableAttributes']);
        self::assertSame(self::DEFAULT_SEARCHABLE_ATTRIBUTES, $settings['displayedAttributes']);
        self::assertEmpty($settings['stopWords']);
        self::assertIsArray($settings['synonyms']);
        self::assertEmpty($settings['synonyms']);
        self::assertIsArray($settings['filterableAttributes']);
        self::assertEmpty($settings['filterableAttributes']);
        self::assertEmpty($settings['sortableAttributes']);
        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $settings['typoTolerance']);
        self::assertSame(self::DEFAULT_FACET_SEARCH, $settings['facetSearch']);
        self::assertSame(self::DEFAULT_PREFIX_SEARCH, $settings['prefixSearch']);
    }
}
