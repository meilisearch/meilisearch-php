<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

use DateTime;
use MeiliSearch\Exceptions\InvalidArgumentException;
use MeiliSearch\Http\Serialize\Json;

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

    public function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function generateTenantToken($searchRules, ?DateTime $expiresAt = null, ?string $apiKey = null): string
    {
        // Validate every fields
        if (null == $apiKey && null == $this->apiKey) {
            throw InvalidArgumentException::emptyArgument('api key');
        }
        if (null == $searchRules) {
            throw InvalidArgumentException::emptyArgument('search rules');
        }
        if ($expiresAt && new DateTime() > $expiresAt) {
            throw InvalidArgumentException::dateIsExpired($expiresAt);
        }

        $json = new Json();

        // Standard JWT header for encryption with SHA256/HS256 algorithm
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
          ];

        if (null == $apiKey) {
            $apiKey = $this->apiKey;
        }

        // Add the required fields to the payload
        $payload['apiKeyPrefix'] = substr($apiKey, 0, 8);
        $payload['searchRules'] = $searchRules;
        if ($expiresAt) {
            $payload['exp'] = $expiresAt->getTimestamp();
        }

        // Serialize the Header
        $jsonHeader = $json->serialize($header);

        // Serialize the Payload
        $jsonPayload = $json->serialize($payload);

        // Encode Header to Base64Url String
        $encodedHeader = $this->base64url_encode($jsonHeader);

        // Encode Payload to Base64Url String
        $encodedPayload = $this->base64url_encode($jsonPayload);

        // Create Signature Hash
        $signature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $apiKey, true);

        // Encode Signature to Base64Url String
        $encodedSignature = $this->base64url_encode($signature);

        // Create JWT
        $jwtToken = $encodedHeader.'.'.$encodedPayload.'.'.$encodedSignature;

        return $jwtToken;
    }
}
