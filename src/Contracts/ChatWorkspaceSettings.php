<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type ChatWorkspaceSource 'openAi'|'azureOpenAi'|'mistral'|'gemini'|'vLlm'
 */
class ChatWorkspaceSettings extends Data
{
    /**
     * @var ChatWorkspaceSource
     */
    private string $source;
    /**
     * @var non-empty-string|null
     */
    private ?string $orgId;
    /**
     * @var non-empty-string|null
     */
    private ?string $projectId;
    /**
     * @var non-empty-string|null
     */
    private ?string $apiVersion;
    /**
     * @var non-empty-string|null
     */
    private ?string $deploymentId;
    /**
     * @var non-empty-string|null
     */
    private ?string $baseUrl;
    private ?string $apiKey;
    private ChatWorkspacePromptsSettings $prompts;

    /**
     * @param array{
     *     source: ChatWorkspaceSource,
     *     orgId?: non-empty-string,
     *     projectId?: non-empty-string,
     *     apiVersion?: non-empty-string,
     *     deploymentId?: non-empty-string,
     *     baseUrl?: non-empty-string,
     *     apiKey?: string,
     *     prompts: array{
     *         system: string,
     *         searchDescription: string,
     *         searchQParam: non-empty-string,
     *         searchIndexUidParam: non-empty-string
     *     }
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->source = $params['source'];
        $this->orgId = $params['orgId'] ?? null;
        $this->projectId = $params['projectId'] ?? null;
        $this->apiVersion = $params['apiVersion'] ?? null;
        $this->deploymentId = $params['deploymentId'] ?? null;
        $this->baseUrl = $params['baseUrl'] ?? null;
        $this->apiKey = $params['apiKey'] ?? null;
        $this->prompts = new ChatWorkspacePromptsSettings($params['prompts']);
    }

    /**
     * @return ChatWorkspaceSource
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return non-empty-string|null
     */
    public function getOrgId(): ?string
    {
        return $this->orgId;
    }

    /**
     * @return non-empty-string|null
     */
    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    /**
     * @return non-empty-string|null
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    /**
     * @return non-empty-string|null
     */
    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    /**
     * @return non-empty-string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @return non-empty-string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getPrompts(): ChatWorkspacePromptsSettings
    {
        return $this->prompts;
    }

    /**
     * @return array{
     *     source: ChatWorkspaceSource,
     *     orgId?: non-empty-string,
     *     projectId?: non-empty-string,
     *     apiVersion?: non-empty-string,
     *     deploymentId?: non-empty-string,
     *     baseUrl?: non-empty-string,
     *     apiKey?: string,
     *     prompts: array{
     *         system: string,
     *         searchDescription: string,
     *         searchQParam: non-empty-string,
     *         searchIndexUidParam: non-empty-string
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'orgId' => $this->orgId,
            'projectId' => $this->projectId,
            'apiVersion' => $this->apiVersion,
            'deploymentId' => $this->deploymentId,
            'baseUrl' => $this->baseUrl,
            'apiKey' => $this->apiKey,
            'prompts' => $this->prompts->toArray(),
        ];
    }
}
