<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\ChatWorkspaceSettings;

trait HandlesChatWorkspaceSettings
{
    /**
     * Get the current settings for this chat workspace.
     */
    public function getSettings(): ChatWorkspaceSettings
    {
        if (!$this->workspaceName) {
            throw new \InvalidArgumentException('Workspace name is required to get settings');
        }

        $response = $this->http->get('/chats/'.$this->workspaceName.'/settings');

        return new ChatWorkspaceSettings($response);
    }

    /**
     * Update the settings for this chat workspace.
     *
     * @param array{
     *     source?: 'openAi'|'azureOpenAi'|'mistral'|'gemini'|'vLlm',
     *     orgId?: string,
     *     projectId?: string,
     *     apiVersion?: string,
     *     deploymentId?: string,
     *     baseUrl?: string,
     *     apiKey?: string,
     *     prompts?: array{
     *         system?: string,
     *         searchDescription?: string,
     *         searchQParam?: string,
     *         searchIndexUidParam?: string
     *     }
     * } $settings
     */
    public function updateSettings(array $settings): ChatWorkspaceSettings
    {
        if (!$this->workspaceName) {
            throw new \InvalidArgumentException('Workspace name is required to update settings');
        }

        $response = $this->http->patch('/chats/'.$this->workspaceName.'/settings', $settings);

        return new ChatWorkspaceSettings($response);
    }

    /**
     * Reset the settings for this chat workspace to default values.
     */
    public function resetSettings(): ChatWorkspaceSettings
    {
        if (!$this->workspaceName) {
            throw new \InvalidArgumentException('Workspace name is required to reset settings');
        }

        $response = $this->http->delete('/chats/'.$this->workspaceName.'/settings');

        return new ChatWorkspaceSettings($response);
    }
}
