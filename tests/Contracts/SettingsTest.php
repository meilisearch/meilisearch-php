<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Index\Settings;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    public function testToArrayPreservesNullableNestedSettings(): void
    {
        $rawSettings = [
            'synonyms' => null,
            'typoTolerance' => null,
            'faceting' => null,
            'embedders' => null,
        ];

        $settings = new Settings($rawSettings);

        $settingsArray = $settings->toArray();

        self::assertNull($settingsArray['synonyms']);
        self::assertNull($settingsArray['typoTolerance']);
        self::assertNull($settingsArray['faceting']);
        self::assertNull($settingsArray['embedders']);
    }

    public function testToArrayOmitsAbsentNestedSettings(): void
    {
        $settings = new Settings([
            'rankingRules' => ['words'],
        ]);

        $settingsArray = $settings->toArray();

        self::assertSame(['words'], $settingsArray['rankingRules']);
        self::assertArrayNotHasKey('synonyms', $settingsArray);
        self::assertArrayNotHasKey('typoTolerance', $settingsArray);
        self::assertArrayNotHasKey('faceting', $settingsArray);
        self::assertArrayNotHasKey('embedders', $settingsArray);
    }

    public function testToArrayReturnsPlainNestedArrays(): void
    {
        $settings = new Settings([
            'synonyms' => [
                'hp' => ['harry potter', 'half-blood prince'],
            ],
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => [
                    'oneTypo' => 4,
                    'twoTypos' => 8,
                ],
                'disableOnWords' => ['hp'],
                'disableOnAttributes' => ['title'],
                'disableOnNumbers' => false,
            ],
            'faceting' => [
                'maxValuesPerFacet' => 50,
                'sortFacetValuesBy' => [
                    '*' => 'count',
                ],
            ],
            'embedders' => [
                'default' => [
                    'source' => 'openAi',
                    'model' => 'text-embedding-3-small',
                    'documentTemplate' => '{{doc.title}}',
                ],
            ],
        ]);

        $settingsArray = $settings->toArray();

        self::assertSame([
            'hp' => ['harry potter', 'half-blood prince'],
        ], $settingsArray['synonyms']);
        self::assertSame([
            'enabled' => true,
            'minWordSizeForTypos' => [
                'oneTypo' => 4,
                'twoTypos' => 8,
            ],
            'disableOnWords' => ['hp'],
            'disableOnAttributes' => ['title'],
            'disableOnNumbers' => false,
        ], $settingsArray['typoTolerance']);
        self::assertSame([
            'maxValuesPerFacet' => 50,
            'sortFacetValuesBy' => [
                '*' => 'count',
            ],
        ], $settingsArray['faceting']);
        self::assertSame([
            'default' => [
                'source' => 'openAi',
                'model' => 'text-embedding-3-small',
                'documentTemplate' => '{{doc.title}}',
            ],
        ], $settingsArray['embedders']);
    }
}
