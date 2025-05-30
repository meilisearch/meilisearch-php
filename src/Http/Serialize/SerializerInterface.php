<?php

declare(strict_types=1);

namespace Meilisearch\Http\Serialize;

interface SerializerInterface
{
    /**
     * Serialize data into string.
     *
     * @param string|int|float|bool|array<mixed>|null $data
     *
     * @return string|bool
     *
     * @throws JsonEncodingException
     */
    public function serialize($data);

    /**
     * Unserialize the given string.
     *
     * @return string|int|float|bool|array<mixed>|null
     *
     * @throws JsonDecodingException
     */
    public function unserialize(string $string);
}
