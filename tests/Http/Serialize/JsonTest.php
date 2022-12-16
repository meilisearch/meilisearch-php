<?php

declare(strict_types=1);

namespace Tests\Http\Serialize;

use Meilisearch\Exceptions\JsonDecodingException;
use Meilisearch\Exceptions\JsonEncodingException;
use Meilisearch\Http\Serialize\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testSerialize(): void
    {
        $data = ['id' => 287947, 'title' => 'Some ID'];
        $json = new Json();
        $this->assertEquals(json_encode($data), $json->serialize($data));
    }

    public function testSerializeWithInvalidData(): void
    {
        $data = ['id' => NAN, 'title' => NAN];
        $json = new Json();
        $this->expectException(JsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Inf and NaN cannot be JSON encoded".');
        $this->assertEquals(json_encode($data), $json->serialize($data));
    }

    public function testUnserialize(): void
    {
        $data = '{"id":287947,"title":"Some ID"}';
        $json = new Json();
        $this->assertEquals(['id' => 287947, 'title' => 'Some ID'], $json->unserialize($data));
    }

    public function testUnserializeWithInvalidData(): void
    {
        $data = "{'id':287947,'title':'\xB1\x31'}";
        $json = new Json();
        $this->expectException(JsonDecodingException::class);
        $this->expectExceptionMessage('Decoding payload to json failed: "Syntax error"');
        $this->assertEquals(['id' => 287947, 'title' => 'Some ID'], $json->unserialize($data));
    }
}
