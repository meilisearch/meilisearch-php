<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\ExportIndexOptions;
use Meilisearch\Contracts\ExportQuery;
use PHPUnit\Framework\TestCase;

final class ExportQueryTest extends TestCase
{
    public function testOnlyUrl(): void
    {
        $query = new ExportQuery('http://example.com');

        self::assertSame(['url' => 'http://example.com'], $query->toArray());
        self::assertSame('{"url":"http://example.com"}', json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES));
    }

    public function testAllFields(): void
    {
        $query = (new ExportQuery('http://example.com'))
            ->setApiKey('secret')
            ->setPayloadSize('50 MiB')
            ->setIndexes([
                'movies*' => (new ExportIndexOptions())->setFilter('genre = romance')->setOverrideSettings(true),
                'books' => (new ExportIndexOptions())->setFilter(['author = Tolkien']),
            ]);

        self::assertSame(
            '{"url":"http://example.com","apiKey":"secret","payloadSize":"50 MiB","indexes":{"movies*":{"filter":"genre = romance","overrideSettings":true},"books":{"filter":["author = Tolkien"]}}}',
            json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES),
        );
    }

    public function testIntegerPayloadSize(): void
    {
        $query = (new ExportQuery('http://example.com'))->setPayloadSize(1048576);

        self::assertSame(['url' => 'http://example.com', 'payloadSize' => 1048576], $query->toArray());
        self::assertSame('{"url":"http://example.com","payloadSize":1048576}', json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES));
    }

    public function testEmptyIndexesEncodeAsJsonObject(): void
    {
        $query = (new ExportQuery('http://example.com'))->setIndexes([]);

        self::assertSame(['url' => 'http://example.com', 'indexes' => []], $query->toArray());
        self::assertSame('{"url":"http://example.com","indexes":{}}', json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES));
    }

    public function testEmptyIndexOptionsEncodeAsJsonObject(): void
    {
        $query = (new ExportQuery('http://example.com'))->setIndexes(['movies*' => new ExportIndexOptions()]);

        self::assertSame('{"url":"http://example.com","indexes":{"movies*":{}}}', json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES));
    }

    public function testNullSettersDropKeys(): void
    {
        $query = (new ExportQuery('http://example.com'))
            ->setApiKey('secret')
            ->setPayloadSize('50 MiB')
            ->setIndexes(['*' => new ExportIndexOptions()])
            ->setApiKey(null)
            ->setPayloadSize(null)
            ->setIndexes(null);

        self::assertSame(['url' => 'http://example.com'], $query->toArray());
        self::assertSame('{"url":"http://example.com"}', json_encode($query, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES));
    }

    public function testToArrayReturnsPlainArrays(): void
    {
        $query = (new ExportQuery('http://example.com'))
            ->setApiKey('secret')
            ->setIndexes([
                'movies*' => (new ExportIndexOptions())->setFilter('genre = romance')->setOverrideSettings(false),
                '*' => new ExportIndexOptions(),
            ]);

        self::assertSame([
            'url' => 'http://example.com',
            'apiKey' => 'secret',
            'indexes' => [
                'movies*' => ['filter' => 'genre = romance', 'overrideSettings' => false],
                '*' => [],
            ],
        ], $query->toArray());
    }
}
