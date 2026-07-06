<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\DocumentDeletionDetails;
use Meilisearch\Contracts\TaskDetails\DocumentEditionDetails;
use Meilisearch\Contracts\TaskDetails\DumpCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexCompactionDetails;
use Meilisearch\Contracts\TaskDetails\IndexCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexDeletionDetails;
use Meilisearch\Contracts\TaskDetails\IndexSwapDetails;
use Meilisearch\Contracts\TaskDetails\IndexUpdateDetails;
use Meilisearch\Contracts\TaskDetails\SettingsUpdateDetails;
use Meilisearch\Contracts\TaskDetails\TaskCancelationDetails;
use Meilisearch\Contracts\TaskDetails\TaskDeletionDetails;
use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
use Meilisearch\Exceptions\LogicException;

/**
 * Raw detail shapes are imported here so Task can choose the concrete detail
 * class while keeping shape validation at this boundary.
 *
 * @phpstan-import-type RawDocumentAdditionOrUpdateDetails from DocumentAdditionOrUpdateDetails
 * @phpstan-import-type RawDocumentDeletionDetails from DocumentDeletionDetails
 * @phpstan-import-type RawDocumentEditionDetails from DocumentEditionDetails
 * @phpstan-import-type RawDumpCreationDetails from DumpCreationDetails
 * @phpstan-import-type RawIndexCompactionDetails from IndexCompactionDetails
 * @phpstan-import-type RawIndexCreationDetails from IndexCreationDetails
 * @phpstan-import-type RawIndexDeletionDetails from IndexDeletionDetails
 * @phpstan-import-type RawIndexSwapDetails from IndexSwapDetails
 * @phpstan-import-type RawIndexUpdateDetails from IndexUpdateDetails
 * @phpstan-import-type RawSettingsUpdateDetails from SettingsUpdateDetails
 * @phpstan-import-type RawTaskCancelationDetails from TaskCancelationDetails
 * @phpstan-import-type RawTaskDeletionDetails from TaskDeletionDetails
 */
final class Task implements \ArrayAccess
{
    /**
     * @param non-negative-int                   $taskUid
     * @param non-empty-string|null              $indexUid
     * @param non-empty-string|null              $duration
     * @param \Closure(int, int, int): Task|null $await
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
        private readonly array $raw = [],
        private readonly ?\Closure $await = null,
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

    /**
     * @return non-empty-string|null
     */
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

    public function wait(int $timeoutInMs = 5000, int $intervalInMs = 50): Task
    {
        if ($this->isFinished()) {
            return $this;
        }

        if (null !== $this->await) {
            return ($this->await)($this->taskUid, $timeoutInMs, $intervalInMs);
        }

        throw new LogicException(\sprintf('Cannot wait for task because wait function is not provided.'));
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('The Task object is immutable.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->raw);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('The Task object is immutable.');
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->raw[$offset] ?? null;
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
     * @param \Closure(int, int, int): Task|null $await
     */
    public static function fromArray(array $data, ?\Closure $await = null): Task
    {
        $details = $data['details'] ?? null;
        $type = TaskType::tryFrom($data['type']) ?? TaskType::Unknown;
        $error = null;

        if (\array_key_exists('error', $data) && null !== $data['error']) {
            /** @phpstan-var array{message: non-empty-string, code: non-empty-string, type: non-empty-string, link: non-empty-string} $errorData */
            $errorData = $data['error'];
            $error = TaskError::fromArray($errorData);
        }

        return new self(
            $data['taskUid'] ?? $data['uid'],
            $data['indexUid'] ?? null,
            TaskStatus::tryFrom($data['status']) ?? TaskStatus::Unknown,
            $type,
            new \DateTimeImmutable($data['enqueuedAt']),
            \array_key_exists('startedAt', $data) && null !== $data['startedAt'] ? new \DateTimeImmutable($data['startedAt']) : null,
            \array_key_exists('finishedAt', $data) && null !== $data['finishedAt'] ? new \DateTimeImmutable($data['finishedAt']) : null,
            $data['duration'] ?? null,
            $data['canceledBy'] ?? null,
            $data['batchUid'] ?? null,
            self::detailsFromArray($type, $details),
            $error,
            $data,
            $await,
        );
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function detailsFromArray(TaskType $type, ?array $details): ?TaskDetails
    {
        // Unknown task types are forward-compatible: preserve whatever payload we got.
        if (TaskType::Unknown === $type) {
            return UnknownTaskDetails::fromArray($details ?? []);
        }

        // For known task types, null/empty details should map to "no details object".
        if (null === $details || [] === $details) {
            return null;
        }

        // The generic helper keeps switch arms readable while still enforcing each raw shape.
        switch ($type) {
            case TaskType::IndexCreation:
                return self::detailFromArray($details, IndexCreationDetails::fromArray(...));
            case TaskType::IndexUpdate:
                return self::detailFromArray($details, IndexUpdateDetails::fromArray(...));
            case TaskType::IndexDeletion:
                return self::detailFromArray($details, IndexDeletionDetails::fromArray(...));
            case TaskType::IndexSwap:
                return self::detailFromArray($details, IndexSwapDetails::fromArray(...));
            case TaskType::DocumentAdditionOrUpdate:
                return self::detailFromArray($details, DocumentAdditionOrUpdateDetails::fromArray(...));
            case TaskType::DocumentDeletion:
                return self::detailFromArray($details, DocumentDeletionDetails::fromArray(...));
            case TaskType::DocumentEdition:
                return self::detailFromArray($details, DocumentEditionDetails::fromArray(...));
            case TaskType::SettingsUpdate:
                return self::detailFromArray($details, SettingsUpdateDetails::fromArray(...));
            case TaskType::DumpCreation:
                return self::detailFromArray($details, DumpCreationDetails::fromArray(...));
            case TaskType::TaskCancelation:
                return self::detailFromArray($details, TaskCancelationDetails::fromArray(...));
            case TaskType::TaskDeletion:
                return self::detailFromArray($details, TaskDeletionDetails::fromArray(...));
                // It’s intentional that SnapshotCreation tasks don’t have a details object
                // (no SnapshotCreationDetails exists and tests don’t exercise any details)
            case TaskType::SnapshotCreation:
            case TaskType::NetworkTopologyChange:
                return null;
            case TaskType::IndexCompaction:
                return self::detailFromArray($details, IndexCompactionDetails::fromArray(...));
        }
    }

    /**
     * @template TShape of array
     * @template TDetails of TaskDetails
     *
     * @param array<mixed>               $details
     * @param callable(TShape): TDetails $factory
     *
     * @return TDetails
     */
    private static function detailFromArray(array $details, callable $factory): TaskDetails
    {
        // PHPStan cannot infer the precise shape from TaskType in this switch.
        // We cast once here, then delegate to the concrete fromArray() factory.
        /** @var TShape $typedDetails */
        $typedDetails = $details;

        return $factory($typedDetails);
    }
}
