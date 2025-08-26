<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhp;

class PollyfillsTest extends TestCase
{
    #[RequiresPhp('< 8.0')]
    public function testArrayIsList(): void
    {
        self::assertTrue(array_is_list([])); // @phpstan-ignore-line
        self::assertTrue(array_is_list(['foo', 'bar'])); // @phpstan-ignore-line
        self::assertFalse(array_is_list(['foo' => 'bar'])); // @phpstan-ignore-line
        self::assertFalse(array_is_list([0 => 'foo', 'foo' => 'bar'])); // @phpstan-ignore-line
    }
}
