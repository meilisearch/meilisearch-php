<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

final class EmbeddersTest extends TestCase
{
    private Indexes $index;

    private const DEFAULT_EMBEDDER = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultEmbedders(): void
    {
        $response = $this->index->getEmbedders();

        self::assertSame(self::DEFAULT_EMBEDDER, $response);
    }

    public function testUpdateEmbedders(): void
    {
        $newEmbedders = ['manual' => ['source' => 'userProvided', 'dimensions' => 3, 'binaryQuantized' => true]];

        $promise = $this->index->updateEmbedders($newEmbedders);

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);

        $embedders = $this->index->getEmbedders();

        self::assertSame($newEmbedders, $embedders);
    }

    public function testResetEmbedders(): void
    {
        $promise = $this->index->resetEmbedders();

        $this->assertIsValidPromise($promise);

        $this->index->waitForTask($promise['taskUid']);
        $embedders = $this->index->getEmbedders();

        self::assertSame(self::DEFAULT_EMBEDDER, $embedders);
    }

    public function testHuggingFacePooling(): void
    {
        $embedder = [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                'pooling' => 'useModel'
        ];

        $promise = $this->index->updateEmbedders([
            'hf' => [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                'pooling' => 'useModel'
            ]
        ]);

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);

        $embedders = $this->index->getEmbedders();

        self::assertEquals($embedder['source'], $embedders['hf']['source']);
        self::assertEquals($embedder['model'], $embedders['hf']['model']);
        self::assertEquals($embedder['pooling'], $embedders['hf']['pooling']);
    }
}
