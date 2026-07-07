<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\PersonalizeOptions;
use PHPUnit\Framework\TestCase;

final class PersonalizeOptionsTest extends TestCase
{
    public function testEmptyOptions(): void
    {
        $data = new PersonalizeOptions();

        self::assertSame([], $data->toArray());
    }

    public function testSetUserContext(): void
    {
        $data = (new PersonalizeOptions())->setUserContext('The user prefers science fiction movies');

        self::assertSame(['userContext' => 'The user prefers science fiction movies'], $data->toArray());
    }
}
