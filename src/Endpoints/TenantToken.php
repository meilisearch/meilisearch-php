<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use DateTime;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Exceptions\InvalidArgumentException;
use MeiliSearch\Http\Serialize\Json;

class TenantToken extends Endpoint
{
    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Generate a new tenant token.
     *
     * The $options parameter is an array, and the following keys are accepted:
     * - apiKey: The API key parent of the token. If you leave it empty the client API Key will be used.
     * - expiresAt: A DateTime when the key will expire. Note that if an expiresAt value is included it should be in UTC time.
     */
    public function generateTenantToken($searchRules, ?array $options = []): string
    {
        // Validate every field
        if ((!\array_key_exists('apiKey', $options) || '' == $options['apiKey']) && \is_null($this->apiKey)) {
            throw InvalidArgumentException::emptyArgument('api key');
        }
        if (null == $searchRules) {
            throw InvalidArgumentException::emptyArgument('search rules');
        }
        if (\array_key_exists('expiresAt', $options) && new DateTime() > $options['expiresAt']) {
            throw InvalidArgumentException::dateIsExpired($options['expiresAt']);
        }

        $json = new Json();

        // Standard JWT header for encryption with SHA256/HS256 algorithm
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
          ];

        if (!\array_key_exists('apiKey', $options)) {
            $options['apiKey'] = $this->apiKey;
        }

        // Add the required fields to the payload
        $payload = [];
        $payload['apiKeyPrefix'] = substr($options['apiKey'], 0, 8);
        $payload['searchRules'] = $searchRules;
        if (\array_key_exists('expiresAt', $options)) {
            $payload['exp'] = $options['expiresAt']->getTimestamp();
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
        $signature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $options['apiKey'], true);

        // Encode Signature to Base64Url String
        $encodedSignature = $this->base64url_encode($signature);

        // Create JWT
        $jwtToken = $encodedHeader.'.'.$encodedPayload.'.'.$encodedSignature;

        return $jwtToken;
    }
}
