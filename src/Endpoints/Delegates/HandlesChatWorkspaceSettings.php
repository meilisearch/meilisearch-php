<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\ChatWorkspaceSettings;
use Psr\Http\Message\StreamInterface;

trait HandlesChatWorkspaceSettings
{
    /**
     * Get the current settings for this chat workspace.
     */
    public function getSettings(): ChatWorkspaceSettings
    {
        if (null === $this->workspaceName) {
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
     *     prompts?: array<string, string>
     * } $settings
     */
    public function updateSettings(array $settings): ChatWorkspaceSettings
    {
        if (null === $this->workspaceName) {
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
        if (null === $this->workspaceName) {
            throw new \InvalidArgumentException('Workspace name is required to reset settings');
        }

        $response = $this->http->delete('/chats/'.$this->workspaceName.'/settings');

        return new ChatWorkspaceSettings($response);
    }

    /**
     * Create a streaming chat completion using OpenAI-compatible API.
     *
     * @param array{
     *     model: string,
     *     messages: array<array{role: string, content: string}>,
     *     stream: bool
     * } $options The request body for the chat completion
     */
    public function streamCompletion(array $options): StreamInterface
    {
        if (null === $this->workspaceName) {
            throw new \InvalidArgumentException('Workspace name is required for chat completion');
        }

        return $this->http->postStream('/chats/'.$this->workspaceName.'/chat/completions', $options);
    }
}
