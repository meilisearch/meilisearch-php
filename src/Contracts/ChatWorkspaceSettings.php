<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class ChatWorkspaceSettings extends Data
{
    private string $source;
    private ?string $orgId;
    private ?string $projectId;
    private ?string $apiVersion;
    private ?string $deploymentId;
    private ?string $baseUrl;
    private ?string $apiKey;
    private ChatWorkspacePromptsSettings $prompts;

    /**
     * @param array{
     *     source: string,
     *     orgId?: string,
     *     projectId?: string,
     *     apiVersion?: string,
     *     deploymentId?: string,
     *     baseUrl?: string,
     *     apiKey?: string,
     *     prompts: array{
     *         system: string,
     *         searchDescription: string,
     *         searchQParam: string,
     *         searchIndexUidParam: string
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

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOrgId(): ?string
    {
        return $this->orgId;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

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
     *     source: string,
     *     orgId?: string,
     *     projectId?: string,
     *     apiVersion?: string,
     *     deploymentId?: string,
     *     baseUrl?: string,
     *     apiKey?: string,
     *     prompts: array{
     *         system: string,
     *         searchDescription: string,
     *         searchQParam: string,
     *         searchIndexUidParam: string
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
