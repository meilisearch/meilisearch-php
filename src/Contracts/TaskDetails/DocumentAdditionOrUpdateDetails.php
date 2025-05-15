<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     receivedDocuments: non-negative-int,
 *     indexedDocuments: non-negative-int|null
 * }>
 */
final class DocumentAdditionOrUpdateDetails implements TaskDetails
{
    /**
     * @param non-negative-int      $receivedDocuments Number of documents received.
     * @param non-negative-int|null $indexedDocuments  Number of documents indexed. `null` while the task status is enqueued or processing.
     */
    public function __construct(
        public readonly int $receivedDocuments,
        public readonly ?int $indexedDocuments,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['receivedDocuments'],
            $data['indexedDocuments'] ?? null,
        );
    }
}
