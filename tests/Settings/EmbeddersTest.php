<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Contracts\TaskStatus;
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

        $task = $this->index->updateEmbedders($newEmbedders);
        $this->index->waitForTask($task->getTaskUid());

        self::assertSame($newEmbedders, $this->index->getEmbedders());
    }

    public function testResetEmbedders(): void
    {
        $task = $this->index->resetEmbedders();

        $this->index->waitForTask($task->getTaskUid());

        self::assertSame(self::DEFAULT_EMBEDDER, $this->index->getEmbedders());
    }

    public function testHuggingFacePooling(): void
    {
        $embedder = [
            'source' => 'huggingFace',
            'model' => 'sentence-transformers/all-MiniLM-L6-v2',
            'pooling' => 'useModel',
        ];

        $task = $this->index->updateEmbedders([
            'embedder_name' => [
                'source' => 'huggingFace',
                'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                'pooling' => 'useModel',
            ],
        ]);

        $this->index->waitForTask($task->getTaskUid());

        $embedders = $this->index->getEmbedders();

        self::assertSame($embedder['source'], $embedders['embedder_name']['source']);
        self::assertSame($embedder['model'], $embedders['embedder_name']['model']);
        self::assertSame($embedder['pooling'], $embedders['embedder_name']['pooling']);
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
            ],
        ];

        $task = $this->index->updateEmbedders([
            'embedder_name' => $embedder,
        ]);

        self::assertSame(TaskStatus::Enqueued, $task->getStatus());
    }
}
