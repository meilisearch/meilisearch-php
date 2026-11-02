<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * Options applied to the indexes matching one pattern of an export.
 *
 * @phpstan-type RawExportIndexOptions array{
 *     filter?: string|list<string|list<string>>,
 *     overrideSettings?: bool
 * }
 */
final class ExportIndexOptions implements \JsonSerializable
{
    /**
     * @var string|list<string|list<string>>|null
     */
    private string|array|null $filter = null;

    private ?bool $overrideSettings = null;

    /**
     * @param string|list<string|list<string>>|null $filter a filter expression, as a string or an array of strings
     *
     * @return $this
     */
    public function setFilter(string|array|null $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param bool $overrideSettings whether to overwrite the settings of indexes that already exist on the target instance
     *
     * @return $this
     */
    public function setOverrideSettings(bool $overrideSettings): self
    {
        $this->overrideSettings = $overrideSettings;

        return $this;
    }

    /**
     * @return RawExportIndexOptions
     */
    public function toArray(): array
    {
        return array_filter([
            'filter' => $this->filter,
            'overrideSettings' => $this->overrideSettings,
        ], static function ($item) { return null !== $item; });
    }

    public function jsonSerialize(): object
    {
        return (object) $this->toArray();
    }
}
