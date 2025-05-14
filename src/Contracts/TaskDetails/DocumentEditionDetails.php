<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     context: array<non-empty-string, scalar|null>,
 *     deletedDocuments: non-negative-int|null,
 *     editedDocuments: non-negative-int|null,
 *     function: string|null,
 *     originalFilter: string|null
 * }>
 */
final class DocumentEditionDetails implements TaskDetails
{
    /**
     * @param array<non-empty-string, scalar|null> $context
     */
    public function __construct(
        public readonly array $context,
        public readonly ?int $deletedDocuments,
        public readonly ?int $editedDocuments,
        public readonly ?string $function,
        public readonly ?string $originalFilter,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['context'],
            $data['deletedDocuments'],
            $data['editedDocuments'],
            $data['function'],
            $data['originalFilter'],
        );
    }
}
