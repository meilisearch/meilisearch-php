<?php

declare(strict_types=1);

namespace Contracts;

use Meilisearch\Contracts\FederationOptions;
use PHPUnit\Framework\TestCase;

final class FederationOptionsTest extends TestCase
{
    public function testEmptyOptions(): void
    {
        $data = new FederationOptions();

        self::assertSame([], $data->toArray());
    }

    public function testSetWeight(): void
    {
        $data = (new FederationOptions())->setWeight(2.369);

        self::assertSame(['weight' => 2.369], $data->toArray());
    }
}
