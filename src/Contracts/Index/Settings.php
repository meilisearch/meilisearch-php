<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;

class Settings extends Data implements \JsonSerializable
{
    public function __construct(array $data = [])
    {
        $data['synonyms'] = new Synonyms($data['synonyms'] ?? []);
        $data['typoTolerance'] = new TypoTolerance($data['typoTolerance'] ?? []);
        $data['faceting'] = new Faceting($data['faceting'] ?? []);
        if (\array_key_exists('embedders', $data)) {
            $data['embedders'] = new Embedders($data['embedders'] ?? []);
        }

        parent::__construct($data);
    }

    public function jsonSerialize(): array
    {
        return $this->getIterator()->getArrayCopy();
    }
}
