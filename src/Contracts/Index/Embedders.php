<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;
use Meilisearch\Contracts\ReadOnlyArrayAccess;

class Embedders extends Data implements \ArrayAccess, \JsonSerializable
{
    use ReadOnlyArrayAccess;

    public function jsonSerialize(): object
    {
        return (object) $this->getIterator()->getArrayCopy();
    }
}
