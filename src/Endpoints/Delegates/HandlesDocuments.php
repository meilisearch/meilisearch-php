<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\DocumentsQuery;
use Meilisearch\Contracts\DocumentsResults;
use Meilisearch\Exceptions\InvalidArgumentException;

trait HandlesDocuments
{
    public function getDocument($documentId, array $fields = null)
    {
        $this->assertValidDocumentId($documentId);
        $query = isset($fields) ? ['fields' => implode(',', $fields)] : [];

        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$documentId, $query);
    }

    public function getDocuments(DocumentsQuery $options = null): DocumentsResults
    {
        $query = isset($options) ? $options->toArray() : [];
        $response = $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);

        return new DocumentsResults($response);
    }

    public function addDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function addDocumentsJson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/json');
    }

    public function addDocumentsCsv(string $documents, ?string $primaryKey = null, ?string $delimiter = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey, 'csvDelimiter' => $delimiter], 'text/csv');
    }

    public function addDocumentsNdjson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/x-ndjson');
    }

    public function addDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocuments($batch, $primaryKey);
        }

        return $promises;
    }

    public function addDocumentsCsvInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null, ?string $delimiter = null)
    {
        $promises = [];

        foreach (self::batchCsvString($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocumentsCsv($batch, $primaryKey, $delimiter);
        }

        return $promises;
    }

    public function addDocumentsNdjsonInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batchNdjsonString($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocumentsNdjson($batch, $primaryKey);
        }

        return $promises;
    }

    public function updateDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocumentsJson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/json');
    }

    public function updateDocumentsCsv(string $documents, ?string $primaryKey = null, ?string $delimiter = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey, 'csvDelimiter' => $delimiter], 'text/csv');
    }

    public function updateDocumentsNdjson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/x-ndjson');
    }

    public function updateDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocuments($batch, $primaryKey);
        }

        return $promises;
    }

    public function updateDocumentsCsvInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null, ?string $delimiter = null)
    {
        $promises = [];
        foreach (self::batchCsvString($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocumentsCsv($batch, $primaryKey, $delimiter);
        }

        return $promises;
    }

    public function updateDocumentsNdjsonInBatches(string $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batchNdjsonString($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocumentsNdjson($batch, $primaryKey);
        }

        return $promises;
    }

    public function deleteAllDocuments(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents');
    }

    public function deleteDocument($documentId): array
    {
        $this->assertValidDocumentId($documentId);

        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$documentId);
    }

    public function deleteDocuments(array $documents): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $documents);
    }

    private function assertValidDocumentId($documentId): void
    {
        if (!\is_string($documentId) && !\is_int($documentId)) {
            throw InvalidArgumentException::invalidType('documentId', ['string', 'int']);
        }

        if (\is_string($documentId) && '' === trim($documentId)) {
            throw InvalidArgumentException::emptyArgument('documentId');
        }
    }

    private static function batchCsvString(string $documents, int $batchSize): \Generator
    {
        $documents = preg_split("/\r\n|\n|\r/", trim($documents));
        $csvHeader = $documents[0];
        array_shift($documents);
        $batches = array_chunk($documents, $batchSize);

        /** @var array<string> $batch */
        foreach ($batches as $batch) {
            array_unshift($batch, $csvHeader);
            $batch = implode("\n", $batch);

            yield $batch;
        }
    }

    private static function batchNdjsonString(string $documents, int $batchSize): \Generator
    {
        $documents = preg_split("/\r\n|\n|\r/", trim($documents));
        $batches = array_chunk($documents, $batchSize);

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
