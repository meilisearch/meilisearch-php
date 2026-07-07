<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\TemplateRenderQuery;
use PHPUnit\Framework\TestCase;

final class TemplateRenderQueryTest extends TestCase
{
    public function testToArrayWithInlineTemplateAndInlineInput(): void
    {
        $query = new TemplateRenderQuery(
            ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
            ['kind' => 'inlineDocument', 'inline' => ['name' => 'John']],
        );

        $result = $query->toArray();

        self::assertSame([
            'template' => ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
            'input' => ['kind' => 'inlineDocument', 'inline' => ['name' => 'John']],
        ], $result);
    }

    public function testToArrayWithDocumentTemplateAndIndexDocumentInput(): void
    {
        $query = new TemplateRenderQuery(
            ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder'],
            ['kind' => 'indexDocument', 'indexUid' => 'movies', 'id' => '2'],
        );

        $result = $query->toArray();

        self::assertSame([
            'template' => ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder'],
            'input' => ['kind' => 'indexDocument', 'indexUid' => 'movies', 'id' => '2'],
        ], $result);
    }

    public function testToArrayOmitsInputWhenNotSet(): void
    {
        $query = new TemplateRenderQuery(
            ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
            // input omitted — not passed
        );

        $result = $query->toArray();

        self::assertArrayNotHasKey('input', $result);
        self::assertSame([
            'template' => ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
        ], $result);
    }

    public function testToArrayIncludesNullInputWhenExplicitlySet(): void
    {
        $query = new TemplateRenderQuery(
            ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
            null,
        );

        $result = $query->toArray();

        self::assertArrayHasKey('input', $result);
        self::assertNull($result['input']);
    }
}
