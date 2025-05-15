<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\DocumentDeletionDetails;
use Meilisearch\Contracts\TaskDetails\DocumentEditionDetails;
use Meilisearch\Contracts\TaskDetails\DumpCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexDeletionDetails;
use Meilisearch\Contracts\TaskDetails\IndexSwapDetails;
use Meilisearch\Contracts\TaskDetails\IndexUpdateDetails;
use Meilisearch\Contracts\TaskDetails\SettingsUpdateDetails;
use Meilisearch\Contracts\TaskDetails\TaskCancelationDetails;
use Meilisearch\Contracts\TaskDetails\TaskDeletionDetails;

final class Task
{
    /**
     * @param non-negative-int      $taskUid
     * @param non-empty-string|null $indexUid
     */
    public function __construct(
        private readonly int $taskUid,
        private readonly ?string $indexUid,
        private readonly TaskStatus $status,
        private readonly TaskType $type,
        private readonly \DateTimeImmutable $enqueuedAt,
        private readonly ?\DateTimeImmutable $startedAt = null,
        private readonly ?\DateTimeImmutable $finishedAt = null,
        private readonly ?string $duration = null,
        private readonly ?int $canceledBy = null,
        private readonly ?int $batchUid = null,
        private readonly ?TaskDetails $details = null,
        private readonly ?TaskError $error = null,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getTaskUid(): int
    {
        return $this->taskUid;
    }

    /**
     * @return non-empty-string|null
     */
    public function getIndexUid(): ?string
    {
        return $this->indexUid;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getType(): TaskType
    {
        return $this->type;
    }

    public function getEnqueuedAt(): \DateTimeImmutable
    {
        return $this->enqueuedAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function getCanceledBy(): ?int
    {
        return $this->canceledBy;
    }

    public function getBatchUid(): ?int
    {
        return $this->batchUid;
    }

    public function getDetails(): ?TaskDetails
    {
        return $this->details;
    }

    public function getError(): ?TaskError
    {
        return $this->error;
    }

    public function isFinished(): bool
    {
        return TaskStatus::Enqueued !== $this->status && TaskStatus::Processing !== $this->status;
    }

    /**
     * @param array{
     *     taskUid?: int,
     *     uid?: int,
     *     indexUid?: non-empty-string,
     *     status: non-empty-string,
     *     type: non-empty-string,
     *     enqueuedAt: non-empty-string,
     *     startedAt?: non-empty-string|null,
     *     finishedAt?: non-empty-string|null,
     *     duration?: non-empty-string|null,
     *     canceledBy?: int,
     *     batchUid?: int,
     *     details?: array<mixed>|null,
     *     error?: array<mixed>|null
     * } $data
     */
    public static function fromArray(array $data): Task
    {
        $details = $data['details'] ?? null;

        return new self(
            $data['taskUid'] ?? $data['uid'],
            $data['indexUid'] ?? null,
            TaskStatus::from($data['status']),
            $type = TaskType::from($data['type']),
            new \DateTimeImmutable($data['enqueuedAt']),
            \array_key_exists('startedAt', $data) && null !== $data['startedAt'] ? new \DateTimeImmutable($data['startedAt']) : null,
            \array_key_exists('finishedAt', $data) && null !== $data['finishedAt'] ? new \DateTimeImmutable($data['finishedAt']) : null,
            $data['duration'] ?? null,
            $data['canceledBy'] ?? null,
            $data['batchUid'] ?? null,
            match ($type) {
                TaskType::IndexCreation => null !== $details ? IndexCreationDetails::fromArray($details) : null,
                TaskType::IndexUpdate => null !== $details ? IndexUpdateDetails::fromArray($details) : null,
                TaskType::IndexDeletion => null !== $details ? IndexDeletionDetails::fromArray($details) : null,
                TaskType::IndexSwap => null !== $details ? IndexSwapDetails::fromArray($details) : null,
                TaskType::DocumentAdditionOrUpdate => null !== $details ? DocumentAdditionOrUpdateDetails::fromArray($details) : null,
                TaskType::DocumentDeletion => null !== $details ? DocumentDeletionDetails::fromArray($details) : null,
                TaskType::DocumentEdition => null !== $details ? DocumentEditionDetails::fromArray($details) : null,
                TaskType::SettingsUpdate => null !== $details ? SettingsUpdateDetails::fromArray($details) : null,
                TaskType::DumpCreation => null !== $details ? DumpCreationDetails::fromArray($details) : null,
                TaskType::TaskCancelation => null !== $details ? TaskCancelationDetails::fromArray($details) : null,
                TaskType::TaskDeletion => null !== $details ? TaskDeletionDetails::fromArray($details) : null,
                // It’s intentional that SnapshotCreation tasks don’t have a details object
                // (no SnapshotCreationDetails exists and tests don’t exercise any details)
                TaskType::SnapshotCreation => null,
            },
            \array_key_exists('error', $data) && null !== $data['error'] ? TaskError::fromArray($data['error']) : null,
        );
    }
}
