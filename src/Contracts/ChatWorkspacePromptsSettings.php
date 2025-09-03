<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type ChatWorkspacePromptsArray array{
 *     system: string,
 *     searchDescription: string,
 *     searchQParam: non-empty-string,
 *     searchIndexUidParam: non-empty-string
 * }
 */
class ChatWorkspacePromptsSettings extends Data
{
    private string $system;
    private string $searchDescription;
    /**
     * @var non-empty-string
     */
    private string $searchQParam;
    /**
     * @var non-empty-string
     */
    private string $searchIndexUidParam;

    /**
     * @param ChatWorkspacePromptsArray $params
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

    /**
     * @return non-empty-string
     */
    public function getSearchQParam(): string
    {
        return $this->searchQParam;
    }

    /**
     * @return non-empty-string
     */
    public function getSearchIndexUidParam(): string
    {
        return $this->searchIndexUidParam;
    }

    /**
     * @return ChatWorkspacePromptsArray
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
