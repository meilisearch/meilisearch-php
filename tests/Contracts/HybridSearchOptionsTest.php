<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\HybridSearchOptions;
use PHPUnit\Framework\TestCase;

final class HybridSearchOptionsTest extends TestCase
{
    public function testEmptyOptions(): void
    {
        $data = new HybridSearchOptions();

        self::assertSame([], $data->toArray());
    }

    public function testSetSemanticRatio(): void
    {
        $data = (new HybridSearchOptions())->setSemanticRatio(0.5);

        self::assertSame(['semanticRatio' => 0.5], $data->toArray());
    }

    public function testSetEmbedder(): void
    {
        $data = (new HybridSearchOptions())->setEmbedder('default');

        self::assertSame(['embedder' => 'default'], $data->toArray());
    }
}
