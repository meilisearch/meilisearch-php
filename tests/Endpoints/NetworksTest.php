<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Http\Client;
use Tests\TestCase;

final class NetworksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['network' => true]);
    }

    public function testUpdateNetworks(): void
    {
        $networks = [
          "self" => "ms-00",
          "remotes" => [
            "ms-00" => [
              "url" => "http://INSTANCE_URL",
              "searchApiKey" => "INSTANCE_API_KEY"
            ],
            "ms-01" => [
              "url" => "http://ANOTHER_INSTANCE_URL",
              "searchApiKey" => "ANOTHER_INSTANCE_API_KEY"
            ]
          ]
        ];

        $response = $this->client->updateNetwork($networks);
        self::assertEquals($networks['self'], $response->getSelf());
        self::assertEquals($networks['remotes'], $response->getRemotes());

        $response = $this->client->getNetwork();
        self::assertEquals($networks['self'], $response->getSelf());
        self::assertEquals($networks['remotes'], $response->getRemotes());
    }
}
