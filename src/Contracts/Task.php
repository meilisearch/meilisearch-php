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
            \array_key_exists('error', $data) && null !== $data['error'] ? TaskError::fromArray($data['error']) : null,
            $data,
            $await,
        );
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function detailsFromArray(TaskType $type, ?array $details): ?TaskDetails
    {
        return match ($type) {
            TaskType::IndexCreation => self::indexCreationDetails($details),
            TaskType::IndexUpdate => self::indexUpdateDetails($details),
            TaskType::IndexDeletion => self::indexDeletionDetails($details),
            TaskType::IndexSwap => self::indexSwapDetails($details),
            TaskType::DocumentAdditionOrUpdate => self::documentAdditionOrUpdateDetails($details),
            TaskType::DocumentDeletion => self::documentDeletionDetails($details),
            TaskType::DocumentEdition => self::documentEditionDetails($details),
            TaskType::SettingsUpdate => self::settingsUpdateDetails($details),
            TaskType::DumpCreation => self::dumpCreationDetails($details),
            TaskType::TaskCancelation => self::taskCancelationDetails($details),
            TaskType::TaskDeletion => self::taskDeletionDetails($details),
            // It’s intentional that SnapshotCreation tasks don’t have a details object
            // (no SnapshotCreationDetails exists and tests don’t exercise any details)
            TaskType::SnapshotCreation => null,
            TaskType::NetworkTopologyChange => null,
            TaskType::IndexCompaction => self::indexCompactionDetails($details),
            TaskType::Unknown => UnknownTaskDetails::fromArray($details ?? []),
        };
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function indexCreationDetails(?array $details): ?IndexCreationDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{primaryKey: non-empty-string|null} $details */
        return IndexCreationDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function indexUpdateDetails(?array $details): ?IndexUpdateDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{primaryKey: non-empty-string|null} $details */
        return IndexUpdateDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function indexDeletionDetails(?array $details): ?IndexDeletionDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{deletedDocuments: non-negative-int|null} $details */
        return IndexDeletionDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function indexSwapDetails(?array $details): ?IndexSwapDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{swaps: array<array{indexes: mixed, rename: bool}>} $details */
        return IndexSwapDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function documentAdditionOrUpdateDetails(?array $details): ?DocumentAdditionOrUpdateDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{receivedDocuments: non-negative-int, indexedDocuments: non-negative-int|null} $details */
        return DocumentAdditionOrUpdateDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function documentDeletionDetails(?array $details): ?DocumentDeletionDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{providedIds?: non-negative-int, originalFilter?: string|null, deletedDocuments?: non-negative-int|null} $details */
        return DocumentDeletionDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function documentEditionDetails(?array $details): ?DocumentEditionDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{context: array<non-empty-string, scalar|null>, deletedDocuments: non-negative-int|null, editedDocuments: non-negative-int|null, function: string|null, originalFilter: string|null} $details */
        return DocumentEditionDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function settingsUpdateDetails(?array $details): ?SettingsUpdateDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{
         *     dictionary?: list<string>,
         *     displayedAttributes?: list<string>,
         *     distinctAttribute?: string,
         *     embedders?: non-empty-array<non-empty-string, array{
         *         apiKey?: string,
         *         binaryQuantized?: bool,
         *         dimensions?: int,
         *         distribution?: array{mean: float, sigma: float},
         *         documentTemplate?: string,
         *         documentTemplateMaxBytes?: int,
         *         indexingEmbedder?: array{model: string, source: string},
         *         model?: string,
         *         pooling?: string,
         *         request?: array<string, mixed>,
         *         response?: array<string, mixed>,
         *         revision?: string,
         *         searchEmbedder?: array{model: string, source: string},
         *         source?: string,
         *         url?: string
         *     }>,
         *     faceting?: array{maxValuesPerFacet: non-negative-int, sortFacetValuesBy: array<string, 'alpha'|'count'>}|null,
         *     facetSearch?: bool,
         *     filterableAttributes?: list<string|array{attributePatterns: list<string>, features: array{facetSearch: bool, filter: array{equality: bool, comparison: bool}}}>|null,
         *     localizedAttributes?: list<array{locales: list<non-empty-string>, attributePatterns: list<string>}>,
         *     nonSeparatorTokens?: list<string>,
         *     pagination?: array{maxTotalHits: non-negative-int},
         *     prefixSearch?: non-empty-string|null,
         *     proximityPrecision?: 'byWord'|'byAttribute',
         *     rankingRules?: list<non-empty-string>,
         *     searchableAttributes?: list<non-empty-string>,
         *     searchCutoffMs?: non-negative-int,
         *     separatorTokens?: list<string>,
         *     sortableAttributes?: list<non-empty-string>,
         *     stopWords?: list<string>,
         *     synonyms?: array<string, list<string>>,
         *     typoTolerance?: array{
         *         enabled: bool,
         *         minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
         *         disableOnWords: list<string>,
         *         disableOnAttributes: list<string>,
         *         disableOnNumbers: bool
         *     }
         * } $details */
        return SettingsUpdateDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function dumpCreationDetails(?array $details): ?DumpCreationDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{dumpUid: non-empty-string|null} $details */
        return DumpCreationDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function taskCancelationDetails(?array $details): ?TaskCancelationDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{matchedTasks: non-negative-int|null, canceledTasks: non-negative-int|null, originalFilter: string|null} $details */
        return TaskCancelationDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function taskDeletionDetails(?array $details): ?TaskDeletionDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{matchedTasks: non-negative-int|null, deletedTasks: non-negative-int|null, originalFilter: string|null} $details */
        return TaskDeletionDetails::fromArray($details);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function indexCompactionDetails(?array $details): ?IndexCompactionDetails
    {
        if (null === $details || [] === $details) {
            return null;
        }

        /* @var array{preCompactionSize: non-empty-string, postCompactionSize: non-empty-string} $details */
        return IndexCompactionDetails::fromArray($details);
    }
}
