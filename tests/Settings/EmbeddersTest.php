<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class EmbeddersTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_EMBEDDER = null;

    protected function setUp(): void
    {
        parent::setUp();
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['vectorStore' => true]);
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
}
