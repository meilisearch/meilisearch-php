<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\TemplateRenderQuery;
use PHPUnit\Framework\TestCase;

final class TemplateRenderQueryTest extends TestCase
{
    public function testToArrayWithInlineTemplateAndInlineInput(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate(['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'])
            ->setInput(['kind' => 'inlineDocument', 'inline' => ['name' => 'John']]);

        $result = $query->toArray();

        self::assertSame([
            'template' => ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
            'input' => ['kind' => 'inlineDocument', 'inline' => ['name' => 'John']],
        ], $result);
    }

    public function testToArrayWithDocumentTemplateAndIndexDocumentInput(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate(['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder'])
            ->setInput(['kind' => 'indexDocument', 'indexUid' => 'movies', 'id' => '2']);

        $result = $query->toArray();

        self::assertSame([
            'template' => ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder'],
            'input' => ['kind' => 'indexDocument', 'indexUid' => 'movies', 'id' => '2'],
        ], $result);
    }

    public function testToArrayOmitsInputWhenNotSet(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate(['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}']);
        // setInput() never called

        $result = $query->toArray();

        self::assertArrayNotHasKey('input', $result);
        self::assertSame([
            'template' => ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'],
        ], $result);
    }

    public function testToArrayIncludesNullInputWhenExplicitlySet(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate(['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}'])
            ->setInput(null);

        $result = $query->toArray();

        self::assertArrayHasKey('input', $result);
        self::assertNull($result['input']);
    }
}
