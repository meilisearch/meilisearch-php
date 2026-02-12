<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\DocumentsQuery;
use Meilisearch\Contracts\DocumentsResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Tasks;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\InvalidResponseBodyException;

use function Meilisearch\partial;

trait HandlesDocuments
{
    /**
     * @param non-empty-string|int $documentId
     */
    public function getDocument(string|int $documentId, ?array $fields = null): array
    {
        $query = isset($fields) ? ['fields' => implode(',', $fields)] : [];

        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$documentId, $query);
    }

    public function getDocuments(?DocumentsQuery $options = null): DocumentsResults
    {
        try {
            $options = $options ?? new DocumentsQuery();
            $query = $options->toArray();

            if ($options->hasFilter()) {
                $response = $this->http->post(self::PATH.'/'.$this->uid.'/documents/fetch', $query);
            } else {
                $response = $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);
            }

            return new DocumentsResults($response);
        } catch (\Exception $e) {
            throw ApiException::rethrowWithHint($e, __FUNCTION__);
        }
    }

    public function addDocuments(array $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]), partial(Tasks::waitTask(...), $this->http));
    }

    public function addDocumentsJson(string $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/json'), partial(Tasks::waitTask(...), $this->http));
    }

    public function addDocumentsCsv(string $documents, ?string $primaryKey = null, ?string $delimiter = null): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey, 'csvDelimiter' => $delimiter], 'text/csv'), partial(Tasks::waitTask(...), $this->http));
    }

    public function addDocumentsNdjson(string $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/x-ndjson'), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @return list<Task>
     */
    public function addDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null): array
    {
        $promises = [];

        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocuments($batch, $primaryKey);
        }

        return $promises;
    }

    /**
     * @return list<Task>
     */
    public function addDocumentsCsvInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null, ?string $delimiter = null): array
    {
        $promises = [];

        foreach (self::batchCsvString($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocumentsCsv($batch, $primaryKey, $delimiter);
        }

        return $promises;
    }

    /**
     * @return list<Task>
     */
    public function addDocumentsNdjsonInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null): array
    {
        $promises = [];

        foreach (self::batchNdjsonString($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocumentsNdjson($batch, $primaryKey);
        }

        return $promises;
    }

    public function updateDocuments(array $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]), partial(Tasks::waitTask(...), $this->http));
    }

    public function updateDocumentsJson(string $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/json'), partial(Tasks::waitTask(...), $this->http));
    }

    public function updateDocumentsCsv(string $documents, ?string $primaryKey = null, ?string $delimiter = null): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey, 'csvDelimiter' => $delimiter], 'text/csv'), partial(Tasks::waitTask(...), $this->http));
    }

    public function updateDocumentsNdjson(string $documents, ?string $primaryKey = null): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/x-ndjson'), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @return list<Task>
     */
    public function updateDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null): array
    {
        $promises = [];

        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocuments($batch, $primaryKey);
        }

        return $promises;
    }

    /**
     * @return list<Task>
     */
    public function updateDocumentsCsvInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null, ?string $delimiter = null): array
    {
        $promises = [];

        foreach (self::batchCsvString($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocumentsCsv($batch, $primaryKey, $delimiter);
        }

        return $promises;
    }

    /**
     * @return list<Task>
     */
    public function updateDocumentsNdjsonInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null): array
    {
        $promises = [];

        foreach (self::batchNdjsonString($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocumentsNdjson($batch, $primaryKey);
        }

        return $promises;
    }

    /**
     * This is an EXPERIMENTAL feature, which may break without a major version.
     * It's available after Meilisearch v1.10.
     *
     * More info about the feature: https://github.com/orgs/meilisearch/discussions/762
     * More info about experimental features in general: https://www.meilisearch.com/docs/reference/api/experimental_features
     *
     * @param non-empty-string                                                                                       $function
     * @param array{filter?: non-empty-string|list<non-empty-string>|null, context?: array<non-empty-string, mixed>} $options
     */
    public function updateDocumentsByFunction(string $function, array $options = []): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents/edit', array_merge(['function' => $function], $options)), partial(Tasks::waitTask(...), $this->http));
    }

    public function deleteAllDocuments(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/documents'), partial(Tasks::waitTask(...), $this->http));
    }

    public function deleteDocument(string|int $documentId): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$documentId), partial(Tasks::waitTask(...), $this->http));
    }

    public function deleteDocuments(array $options): Task
    {
        try {
            if (\array_key_exists('filter', $options) && $options['filter']) {
                return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents/delete', $options), partial(Tasks::waitTask(...), $this->http));
            }

            // backwards compatibility:
            // expect to be a array to send alongside as $documents_ids.
            return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $options), partial(Tasks::waitTask(...), $this->http));
        } catch (InvalidResponseBodyException $e) {
            throw ApiException::rethrowWithHint($e, __FUNCTION__);
        }
    }

    private static function batchCsvString(string $documents, int $batchSize): \Generator
    {
        $parsedDocuments = preg_split("/\r\n|\n|\r/", trim($documents));
        $csvHeader = $parsedDocuments[0];
        array_shift($parsedDocuments);
        $batches = array_chunk($parsedDocuments, $batchSize);

        /** @var array<string> $batch */
        foreach ($batches as $batch) {
            array_unshift($batch, $csvHeader);
            $batch = implode("\n", $batch);

            yield $batch;
        }
    }

    private static function batchNdjsonString(string $documents, int $batchSize): \Generator
    {
        $parsedDocuments = preg_split("/\r\n|\n|\r/", trim($documents));
        $batches = array_chunk($parsedDocuments, $batchSize);

        /** @var array<string> $batch */
        foreach ($batches as $batch) {
            $batch = implode("\n", $batch);

            yield $batch;
        }
    }

    private static function batch(array $documents, int $batchSize): \Generator
    {
        $batches = array_chunk($documents, $batchSize);

        foreach ($batches as $batch) {
            yield $batch;
        }
    }
}
