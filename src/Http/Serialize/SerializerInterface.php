<?php

declare(strict_types=1);

namespace Meilisearch\Http\Serialize;

use Meilisearch\Exceptions\JsonDecodingException;
use Meilisearch\Exceptions\JsonEncodingException;

interface SerializerInterface
{
    /**
     * Serialize data into string.
     *
     * @throws JsonEncodingException
     */
    public function serialize(mixed $data): string|bool;

    /**
     * Unserialize the given string.
     *
     * @throws JsonDecodingException
     */
    public function unserialize(mixed $string): mixed;
}
