<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Meilisearch\Http\Client;
use Tests\TestCase;

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
            'embedder_name' => [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                'pooling' => 'useModel'
            ]
        ]);

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);

        $embedders = $this->index->getEmbedders();

        self::assertEquals($embedder['source'], $embedders['embedder_name']['source']);
        self::assertEquals($embedder['model'], $embedders['embedder_name']['model']);
        self::assertEquals($embedder['pooling'], $embedders['embedder_name']['pooling']);
    }

    public function testCompositeEmbedder(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['compositeEmbedders' => true]);

        $embedder = [
            'source' => 'composite',
            'searchEmbedder' => [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
            ],
            'indexingEmbedder' => [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
            ]
        ];

        $promise = $this->index->updateEmbedders([
            'embedder_name' => $embedder
        ]);

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);

        $embedders = $this->index->getEmbedders();

    }
}
