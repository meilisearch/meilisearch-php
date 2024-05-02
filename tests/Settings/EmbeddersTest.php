<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Http\Client;
use Tests\TestCase;

final class EmbeddersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['vectorStore' => true]);
    }

    public function testGetEmbedders(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));
        $embedders = $index->getEmbedders();

        self::assertEmpty($embedders);
    }

    public function testUpdateEmbeddersWithOpenAi(): void
    {
        $embedderConfig = [
            'source' => 'openAi',
            'model' => 'text-embedding-ada-002',
            'apiKey' => '<your-OpenAI-API-key>',
            'documentTemplate' => "A movie titled '{{doc.title}}' whose description starts with {{doc.overview|truncatewords: 20}}",
        ];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateEmbedders(['myEmbedder' => $embedderConfig]);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $embedders = $index->getEmbedders();
        $embedderConfig['apiKey'] = '<yoXXXXX...';

        self::assertSame($embedderConfig, $embedders['myEmbedder']);
    }

    public function testUpdateEmbeddersWithUserProvided(): void
    {
        $embedderConfig = [
            'source' => 'userProvided',
            'dimensions' => 1,
            'distribution' => ['mean' => 0.7, 'sigma' => 0.3]
        ];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateEmbedders(['myEmbedder' => $embedderConfig]);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $embedders = $index->getEmbedders();

        self::assertSame($embedderConfig, $embedders['myEmbedder']);
    }

    public function testUpdateEmbeddersWithHuggingFace(): void
    {
        $embedderConfig = [
            'source' => 'huggingFace',
            'model' => 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2',
            'documentTemplate' => "A movie titled '{{doc.title}}' whose description starts with {{doc.overview|truncatewords: 20}}",
            'distribution' => ['mean' => 0.7, 'sigma' => 0.3]
        ];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateEmbedders(['myEmbedder' => $embedderConfig]);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $embedders = $index->getEmbedders();

        self::assertSame($embedderConfig, $embedders['myEmbedder']);
    }

    public function testResetEmbedders(): void
    {
        $embedderConfig = [
            'source' => 'userProvided',
            'dimensions' => 1,
        ];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateEmbedders(['myEmbedder' => $embedderConfig]);
        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetEmbedders();
        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        self::assertEmpty($index->getEmbedders());
    }
}
