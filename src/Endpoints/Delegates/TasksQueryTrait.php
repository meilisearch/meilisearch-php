<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

trait TasksQueryTrait
{
    /**
     * @var list<non-empty-string>
     */
    private ?array $types = null;
    /**
     * @var list<non-empty-string>
     */
    private ?array $statuses = null;
    /**
     * @var list<non-empty-string>
     */
    private ?array $indexUids = null;
    /**
     * @var list<int>
     */
    private ?array $uids = null;
    private ?\DateTimeInterface $beforeEnqueuedAt = null;
    private ?\DateTimeInterface $afterEnqueuedAt = null;
    private ?\DateTimeInterface $beforeStartedAt = null;
    private ?\DateTimeInterface $afterStartedAt = null;
    private ?\DateTimeInterface $beforeFinishedAt = null;
    private ?\DateTimeInterface $afterFinishedAt = null;

    /**
     * @param list<non-empty-string>|null $types
     *
     * @return $this
     */
    public function setTypes(?array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @param list<non-empty-string>|null $statuses
     *
     * @return $this
     */
    public function setStatuses(?array $statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @param list<non-empty-string>|null $indexUids
     *
     * @return $this
     */
    public function setIndexUids(?array $indexUids): self
    {
        $this->indexUids = $indexUids;

        return $this;
    }

    /**
     * @return list<non-empty-string>
     */
    public function getIndexUids(): array
    {
        return $this->indexUids ?? [];
    }

    /**
     * @param list<int>|null $uids
     *
     * @return $this
     */
    public function setUids(?array $uids): self
    {
        $this->uids = $uids;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeEnqueuedAt(?\DateTimeInterface $date): self
    {
        $this->beforeEnqueuedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterEnqueuedAt(?\DateTimeInterface $date): self
    {
        $this->afterEnqueuedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeStartedAt(?\DateTimeInterface $date): self
    {
        $this->beforeStartedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterStartedAt(?\DateTimeInterface $date): self
    {
        $this->afterStartedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeFinishedAt(?\DateTimeInterface $date): self
    {
        $this->beforeFinishedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterFinishedAt(?\DateTimeInterface $date): self
    {
        $this->afterFinishedAt = $date;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            $this->baseArray(),
            static function ($item) { return null !== $item; }
        );
    }

    protected function baseArray(): array
    {
        return [
            'beforeEnqueuedAt' => $this->formatDate($this->beforeEnqueuedAt),
            'afterEnqueuedAt' => $this->formatDate($this->afterEnqueuedAt),
            'beforeStartedAt' => $this->formatDate($this->beforeStartedAt),
            'afterStartedAt' => $this->formatDate($this->afterStartedAt),
            'beforeFinishedAt' => $this->formatDate($this->beforeFinishedAt),
            'afterFinishedAt' => $this->formatDate($this->afterFinishedAt),
            'statuses' => $this->formatArray($this->statuses),
            'uids' => $this->formatArray($this->uids),
            'types' => $this->formatArray($this->types),
            'indexUids' => $this->formatArray($this->indexUids),
        ];
    }

    private function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date?->format(\DateTimeInterface::RFC3339);
    }

    private function formatArray(?array $array): ?string
    {
        return null !== $array ? implode(',', $array) : null;
    }
}
