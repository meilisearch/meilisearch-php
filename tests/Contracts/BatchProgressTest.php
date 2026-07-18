<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\BatchProgress;
use Meilisearch\Contracts\BatchProgressStep;
use PHPUnit\Framework\TestCase;

final class BatchProgressTest extends TestCase
{
    public function testConstruct(): void
    {
        $steps = [
            new BatchProgressStep(
                currentStep: 'indexing',
                finished: 1,
                total: 2,
            ),
        ];

        $progress = new BatchProgress(
            steps: $steps,
            percentage: 50.0,
        );

        self::assertSame($steps, $progress->getSteps());
        self::assertSame(50.0, $progress->getPercentage());
    }

    public function testFromArray(): void
    {
        $progress = BatchProgress::fromArray([
            'steps' => [
                ['currentStep' => 'indexing', 'finished' => 1, 'total' => 2],
            ],
            'percentage' => 50.0,
        ]);

        self::assertEquals([
            new BatchProgressStep(
                currentStep: 'indexing',
                finished: 1,
                total: 2,
            ),
        ], $progress->getSteps());
        self::assertSame(50.0, $progress->getPercentage());
    }

    public function testFromArrayMapsMultipleSteps(): void
    {
        $progress = BatchProgress::fromArray([
            'steps' => [
                ['currentStep' => 'extractingWords', 'finished' => 2, 'total' => 2],
                ['currentStep' => 'indexing', 'finished' => 1, 'total' => 3],
            ],
            'percentage' => 33.3,
        ]);

        self::assertEquals([
            new BatchProgressStep(
                currentStep: 'extractingWords',
                finished: 2,
                total: 2,
            ),
            new BatchProgressStep(
                currentStep: 'indexing',
                finished: 1,
                total: 3,
            ),
        ], $progress->getSteps());
        self::assertSame(33.3, $progress->getPercentage());
    }

    public function testProgressStepConstruct(): void
    {
        $step = new BatchProgressStep(
            currentStep: 'indexing',
            finished: 1,
            total: 2,
        );

        self::assertSame('indexing', $step->getCurrentStep());
        self::assertSame(1, $step->getFinished());
        self::assertSame(2, $step->getTotal());
    }

    public function testProgressStepFromArray(): void
    {
        $step = BatchProgressStep::fromArray([
            'currentStep' => 'indexing',
            'finished' => 1,
            'total' => 2,
        ]);

        self::assertSame('indexing', $step->getCurrentStep());
        self::assertSame(1, $step->getFinished());
        self::assertSame(2, $step->getTotal());
    }
}
