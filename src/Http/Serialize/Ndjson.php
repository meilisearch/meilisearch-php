<?php

declare(strict_types=1);

namespace Meilisearch\Http\Serialize;

use Meilisearch\Exceptions\JsonDecodingException;
use Meilisearch\Exceptions\JsonEncodingException;

class Ndjson implements SerializerInterface
{
    private const NDJSON_ENCODE_ERROR_MESSAGE = 'Encoding payload to NDJSON failed: "%s".';
    private const NDJSON_DECODE_ERROR_MESSAGE = 'Decoding payload to NDJSON failed: "%s".';

    public function serialize($data)
    {
        try {
            $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonEncodingException(\sprintf(self::NDJSON_ENCODE_ERROR_MESSAGE, $e->getMessage()), $e->getCode(), $e);
        }

        return $encoded."\n";
    }

    public function unserialize(string $string)
    {
        try {
            $decoded = json_decode(trim($string), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonDecodingException(\sprintf(self::NDJSON_DECODE_ERROR_MESSAGE, $e->getMessage()), $e->getCode(), $e);
        }

        return $decoded;
    }
}
