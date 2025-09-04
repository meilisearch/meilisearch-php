<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\ChatWorkspacesResults;
use Meilisearch\Endpoints\ChatWorkspaces;

trait HandlesChatWorkspaces
{
    private ChatWorkspaces $chats;

    /**
     * List all chat workspaces.
     */
    public function getChatWorkspaces(): ChatWorkspacesResults
    {
        return $this->chats->listWorkspaces();
    }

    /**
     * Get a specific chat workspace instance.
     */
    public function chatWorkspace(string $workspaceName): ChatWorkspaces
    {
        return $this->chats->workspace($workspaceName);
    }
}
