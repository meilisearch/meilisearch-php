<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\ExportIndexOptions;
use PHPUnit\Framework\TestCase;

final class ExportIndexOptionsTest extends TestCase
{
    public function testEmptyOptionsEncodeAsJsonObject(): void
    {
        $options = new ExportIndexOptions();

        self::assertSame([], $options->toArray());
        self::assertSame('{}', json_encode($options, \JSON_THROW_ON_ERROR));
    }

    public function testStringFilterWithOverrideSettings(): void
    {
        $options = (new ExportIndexOptions())
            ->setFilter('genre = romance')
            ->setOverrideSettings(true);

        self::assertSame(['filter' => 'genre = romance', 'overrideSettings' => true], $options->toArray());
        self::assertSame('{"filter":"genre = romance","overrideSettings":true}', json_encode($options, \JSON_THROW_ON_ERROR));
    }

    public function testOverrideSettingsFalseIsEmitted(): void
    {
        $options = (new ExportIndexOptions())->setOverrideSettings(false);

        self::assertSame(['overrideSettings' => false], $options->toArray());
        self::assertSame('{"overrideSettings":false}', json_encode($options, \JSON_THROW_ON_ERROR));
    }

    public function testNestedArrayFilterRoundTrips(): void
    {
        $filter = [['a = 1', 'b = 2'], 'c = 3'];

        $options = (new ExportIndexOptions())->setFilter($filter);

        self::assertSame(['filter' => $filter], $options->toArray());
        self::assertSame('{"filter":[["a = 1","b = 2"],"c = 3"]}', json_encode($options, \JSON_THROW_ON_ERROR));
    }

    public function testNullFilterIsOmitted(): void
    {
        $options = (new ExportIndexOptions())->setFilter('genre = romance')->setFilter(null);

        self::assertSame([], $options->toArray());
        self::assertSame('{}', json_encode($options, \JSON_THROW_ON_ERROR));
    }
}
