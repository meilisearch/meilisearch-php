<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;

class Faceting extends Data implements \JsonSerializable
{
    public function jsonSerialize(): object
    {
        return (object) $this->getIterator()->getArrayCopy();
    }
}
