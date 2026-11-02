<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * Index keys are the requested patterns while the task is enqueued, processing or failed.
 * Once it succeeds they become the matched index uids, and the snake_case `matched_documents`
 * key returned by the server is added to each entry.
 *
 * @phpstan-type RawExportIndexDetails array{
 *     filter: string|list<string|list<string>>|null,
 *     overrideSettings: bool,
 *     matched_documents?: non-negative-int
 * }
 * @phpstan-type RawExportDetails array{
 *     url: string,
 *     apiKey?: non-empty-string,
 *     payloadSize?: non-empty-string,
 *     indexes?: array<non-empty-string, RawExportIndexDetails>
 * }
 *
 * @implements TaskDetails<RawExportDetails>
 */
final class ExportDetails implements TaskDetails
{
    /**
     * @param non-empty-string|null                          $apiKey      masked by the server, e.g. "XXX..."
     * @param non-empty-string|null                          $payloadSize human-readable size, e.g. "50 MiB"
     * @param array<non-empty-string, RawExportIndexDetails> $indexes
     */
    public function __construct(
        public readonly string $url,
        public readonly ?string $apiKey,
        public readonly ?string $payloadSize,
        public readonly array $indexes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['url'],
            $data['apiKey'] ?? null,
            $data['payloadSize'] ?? null,
            $data['indexes'] ?? [],
        );
    }
}
