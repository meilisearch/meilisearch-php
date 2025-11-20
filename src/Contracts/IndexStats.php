<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class IndexStats
{
    /**
     * @param non-negative-int                          $numberOfDocuments
     * @param non-negative-int                          $rawDocumentDbSize
     * @param non-negative-int                          $avgDocumentSize
     * @param non-negative-int                          $numberOfEmbeddings
     * @param non-negative-int                          $numberOfEmbeddedDocuments
     * @param array<non-empty-string, non-negative-int> $fieldDistribution
     */
    public function __construct(
        private readonly int $numberOfDocuments,
        private readonly int $rawDocumentDbSize,
        private readonly int $avgDocumentSize,
        private readonly bool $isIndexing,
        private readonly int $numberOfEmbeddings,
        private readonly int $numberOfEmbeddedDocuments,
        private readonly array $fieldDistribution,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfDocuments(): int
    {
        return $this->numberOfDocuments;
    }

    /**
     * @return non-negative-int
     */
    public function getRawDocumentDbSize(): int
    {
        return $this->rawDocumentDbSize;
    }

    /**
     * @return non-negative-int
     */
    public function getAvgDocumentSize(): int
    {
        return $this->avgDocumentSize;
    }

    public function isIndexing(): bool
    {
        return $this->isIndexing;
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfEmbeddings(): int
    {
        return $this->numberOfEmbeddings;
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfEmbeddedDocuments(): int
    {
        return $this->numberOfEmbeddedDocuments;
    }

    /**
     * @return array<non-empty-string, non-negative-int>
     */
    public function getFieldDistribution(): array
    {
        return $this->fieldDistribution;
    }

    /**
     * @param array{
     *     numberOfDocuments: non-negative-int,
     *     rawDocumentDbSize: non-negative-int,
     *     avgDocumentSize: non-negative-int,
     *     isIndexing: bool,
     *     numberOfEmbeddings: non-negative-int,
     *     numberOfEmbeddedDocuments: non-negative-int,
     *     fieldDistribution: array<non-empty-string, non-negative-int>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['numberOfDocuments'],
            $data['rawDocumentDbSize'],
            $data['avgDocumentSize'],
            $data['isIndexing'],
            $data['numberOfEmbeddings'],
            $data['numberOfEmbeddedDocuments'],
            $data['fieldDistribution'],
        );
    }
}
