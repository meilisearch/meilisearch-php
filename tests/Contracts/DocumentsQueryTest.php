<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DocumentsQuery;
use PHPUnit\Framework\TestCase;

final class DocumentsQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new DocumentsQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetFields(): void
    {
        $data = (new DocumentsQuery())->setLimit(10)->setFields(['abc', 'xyz']);

        self::assertSame(['limit' => 10, 'fields' => ['abc', 'xyz']], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new DocumentsQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new DocumentsQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }

    public function testSetRetrieveVectors(): void
    {
        $data = (new DocumentsQuery())->setRetrieveVectors(true);

        self::assertSame(['retrieveVectors' => true], $data->toArray());
    }

    /**
     * @dataProvider idsProvider
     */
    public function testSetIds(array $input, ?string $expected): void
    {
        $data = (new DocumentsQuery())->setIds($input);
        $result = $data->toArray();

        if (null === $expected) {
            self::assertArrayNotHasKey('ids', $result);
        } else {
            self::assertSame($expected, $result['ids']);
        }
    }

    public static function idsProvider(): array
    {
        return [
            'string array' => [['1', '2', '3'], '1,2,3'],
            'integer array' => [[1, 2, 3], '1,2,3'],
            'mixed array' => [['1', 2, '3'], '1,2,3'],
            'empty array' => [[], null],
        ];
    }
}
