<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-import-type RawBatchProgressStep from BatchProgressStep
 *
 * @phpstan-type RawBatchProgress array{
 *     steps: list<RawBatchProgressStep>,
 *     percentage: float
 * }
 */
final class BatchProgress
{
    /**
     * @param list<BatchProgressStep> $steps
     */
    public function __construct(
        private readonly array $steps,
        private readonly float $percentage,
    ) {
    }

    /**
     * @return list<BatchProgressStep>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    /**
     * @param RawBatchProgress $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            array_map(
                static fn (array $step) => BatchProgressStep::fromArray($step),
                $data['steps'],
            ),
            $data['percentage'],
        );
    }
}
