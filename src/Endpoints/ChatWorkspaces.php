<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\ChatWorkspacesResults;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Http;
use Meilisearch\Endpoints\Delegates\HandlesChatWorkspaceSettings;

class ChatWorkspaces extends Endpoint
{
    use HandlesChatWorkspaceSettings;

    protected const PATH = '/chats';

    /**
     * @var non-empty-string|null
     */
    private ?string $workspaceName;

    public function __construct(Http $http, ?string $workspaceName = null)
    {
        $this->workspaceName = $workspaceName;
        parent::__construct($http);
    }

    public function listWorkspaces(): ChatWorkspacesResults
    {
        $response = $this->http->get(self::PATH);

        return new ChatWorkspacesResults($response);
    }

    public function workspace(string $workspaceName): self
    {
        return new self($this->http, $workspaceName);
    }
}
