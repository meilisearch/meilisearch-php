<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class TypoToleranceTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_TYPO_TOLERANCE = [
        'enabled' => true,
        'minWordSizeForTypos' => [
            'oneTypo' => 5,
            'twoTypos' => 9,
        ],
        'disableOnWords' => [],
        'disableOnAttributes' => [],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultTypoTolerance(): void
    {
        $response = $this->index->getTypoTolerance();

        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $response);
    }

    public function testUpdateTypoTolerance(): void
    {
        $newTypoTolerance = [
            'enabled' => true,
            'minWordSizeForTypos' => [
                'oneTypo' => 6,
                'twoTypos' => 10,
            ],
            'disableOnWords' => [],
            'disableOnAttributes' => ['title'],
        ];
        $this->index->updateTypoTolerance($newTypoTolerance)->wait();
        $typoTolerance = $this->index->getTypoTolerance();

        self::assertSame($newTypoTolerance, $typoTolerance);
    }

    public function testResetTypoTolerance(): void
    {
        $this->index->resetTypoTolerance()->wait();
        $typoTolerance = $this->index->getTypoTolerance();

        self::assertSame(self::DEFAULT_TYPO_TOLERANCE, $typoTolerance);
    }
}
