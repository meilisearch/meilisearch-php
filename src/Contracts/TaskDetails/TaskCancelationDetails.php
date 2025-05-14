<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     matchedTasks: non-negative-int|null,
 *     canceledTasks: non-negative-int|null,
 *     originalFilter: string|null
 * }>
 */
final class TaskCancelationDetails implements TaskDetails
{
    /**
     * @param non-negative-int|null $matchedTasks   The number of matched tasks. If the API key used for the request doesn’t have access to an index, tasks relating to that index will not be included in matchedTasks.
     * @param non-negative-int|null $canceledTasks  The number of tasks successfully canceled. If the task cancellation fails, this will be 0. null when the task status is enqueued or processing.
     * @param string|null           $originalFilter the filter used in the cancel task request
     */
    public function __construct(
        public readonly ?int $matchedTasks,
        public readonly ?int $canceledTasks,
        public readonly ?string $originalFilter,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['matchedTasks'],
            $data['canceledTasks'],
            $data['originalFilter'],
        );
    }
}
