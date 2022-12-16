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
     * @param string|int|float|bool|array|null $data
     *
     * @return string|bool
     *
     * @throws JsonEncodingException
     */
    public function serialize($data);

    /**
     * Unserialize the given string.
     *
     * @return string|int|float|bool|array|null
     *
     * @throws JsonDecodingException
     */
    public function unserialize(string $string);
}
