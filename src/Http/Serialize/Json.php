<?php

declare(strict_types=1);

namespace Meilisearch\Http\Serialize;

class Json implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function unserialize(string $string): mixed
    {
        return json_decode($string, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
    }
}
