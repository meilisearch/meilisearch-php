<?php

declare(strict_types=1);

namespace Tests\Http\Serialize;

use Meilisearch\Http\Serialize\Json;
use PHPUnit\Framework\TestCase;
use JsonException;

class JsonTest extends TestCase
{
    public function testSerialize(): void
    {
        $data = ['id' => 287947, 'title' => 'Some ID'];
        $json = new Json();
        self::assertSame(json_encode($data), $json->serialize($data));
    }

    public function testSerializeWithInvalidData(): void
    {
        $data = ['id' => NAN, 'title' => NAN];
        $json = new Json();
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');
        self::assertSame(json_encode($data), $json->serialize($data));
    }

    public function testUnserialize(): void
    {
        $data = '{"id":287947,"title":"Some ID"}';
        $json = new Json();
        self::assertSame(['id' => 287947, 'title' => 'Some ID'], $json->unserialize($data));
    }

    public function testUnserializeWithInvalidData(): void
    {
        $data = "{'id':287947,'title':'\xB1\x31'}";
        $json = new Json();
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error');
        self::assertSame(['id' => 287947, 'title' => 'Some ID'], $json->unserialize($data));
    }
}
