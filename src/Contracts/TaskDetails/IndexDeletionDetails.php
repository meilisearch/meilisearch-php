<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     deletedDocuments: non-negative-int|null
 * }>
 */
final class IndexDeletionDetails implements TaskDetails
{
    /**
     * @param non-negative-int|null $deletedDocuments Number of deleted documents. This should equal the total number of documents in the deleted index. `null` while the task status is enqueued or processing.
     */
    public function __construct(
        public readonly ?int $deletedDocuments,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['deletedDocuments'],
        );
    }
}
