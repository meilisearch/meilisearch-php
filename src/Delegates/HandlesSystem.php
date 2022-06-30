<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

trait HandlesSystem
{
    public function health(): ?array
    {
        return $this->health->show();
    }

    public function isHealthy(): bool
    {
        try {
            $this->health->show();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function version(): array
    {
        return $this->version->show();
    }

    public function stats(): array
    {
        return $this->stats->show();
    }

    public function generateTenantToken(string $apiKeyUid, $searchRules, ?array $options = []): string
    {
        return $this->tenantToken->generateTenantToken($apiKeyUid, $searchRules, $options);
    }
}
