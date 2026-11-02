<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\ExportIndexOptions;
use Meilisearch\Contracts\ExportQuery;
use Meilisearch\Contracts\TaskDetails\ExportDetails;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Contracts\TaskType;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class ExportTest extends TestCase
{
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $apiKey = getenv('MEILISEARCH_API_KEY');
        if (!$apiKey) {
            throw new \Exception('Missing `MEILISEARCH_API_KEY` environment variable');
        }

        $this->apiKey = $apiKey;
    }

    public function testExportFilteredIndexToRemoteInstance(): void
    {
        $uid = $this->safeIndexName('export');
        $index = $this->createEmptyIndex($uid);
        $index->addDocuments(self::DOCUMENTS)->wait();
        $index->updateFilterableAttributes(['genre'])->wait();

        $task = $this->client->export(
            (new ExportQuery($this->host))
                ->setApiKey($this->apiKey)
                ->setPayloadSize('50 MiB')
                ->setIndexes([
                    $uid => (new ExportIndexOptions())
                        ->setFilter('genre = romance')
                        ->setOverrideSettings(true),
                ])
        );

        self::assertSame(TaskType::Export, $task->getType());

        $task = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());

        $details = $task->getDetails();
        self::assertInstanceOf(ExportDetails::class, $details);
        self::assertSame($this->host, $details->url);
        self::assertSame('XXX...', $details->apiKey);
        self::assertSame('50 MiB', $details->payloadSize);
        self::assertSame(
            ['filter' => 'genre = romance', 'overrideSettings' => true, 'matched_documents' => 3],
            $details->indexes[$uid],
        );
    }

    public function testExportWithInvalidPayloadSizeThrows(): void
    {
        try {
            $this->client->export(
                (new ExportQuery($this->host))
                    ->setApiKey($this->apiKey)
                    ->setPayloadSize('abc')
                    ->setIndexes([$this->safeIndexName('export') => new ExportIndexOptions()])
            );
            self::fail('Expected an ApiException for an invalid payload size.');
        } catch (ApiException $e) {
            self::assertSame(400, $e->httpStatus);
            self::assertSame('invalid_export_payload_size', $e->errorCode);
        }
    }
}
