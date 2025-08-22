<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class ChatWorkspacePromptsSettings extends Data
{
    public string $system;
    public string $searchDescription;
    public string $searchQParam;
    public string $searchIndexUidParam;

    /**
     * @param array{
     *     system: string,
     *     searchDescription: string,
     *     searchQParam: string,
     *     searchIndexUidParam: string
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->system = $params['system'];
        $this->searchDescription = $params['searchDescription'];
        $this->searchQParam = $params['searchQParam'];
        $this->searchIndexUidParam = $params['searchIndexUidParam'];
    }

    public function getSystem(): string
    {
        return $this->system;
    }

    public function getSearchDescription(): string
    {
        return $this->searchDescription;
    }

    public function getSearchQParam(): string
    {
        return $this->searchQParam;
    }

    public function getSearchIndexUidParam(): string
    {
        return $this->searchIndexUidParam;
    }

    /**
     * @return array{
     *     system: string,
     *     searchDescription: string,
     *     searchQParam: string,
     *     searchIndexUidParam: string
     * }
     */
    public function toArray(): array
    {
        return [
            'system' => $this->system,
            'searchDescription' => $this->searchDescription,
            'searchQParam' => $this->searchQParam,
            'searchIndexUidParam' => $this->searchIndexUidParam,
        ];
    }
}
