<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Http\Client;
use Tests\TestCase;

final class SearchRulesTest extends TestCase
{
    private const SEARCH_RULE_UID = 'movie-rule';
    private const SEARCH_RULE_PATCH = [
        'actions' => [
            [
                'selector' => [
                    'indexUid' => 'movies',
                    'id' => '1',
                ],
                'action' => [
                    'type' => 'pin',
                    'position' => 1,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['dynamicSearchRules' => true]);
    }

    protected function tearDown(): void
    {
        $response = $this->client->getDynamicSearchRules();

        foreach ($response['results'] as $rule) {
            $this->client->deleteDynamicSearchRule($rule['uid']);
        }

        parent::tearDown();
    }

    public function testCanListDynamicSearchRules(): void
    {
        $this->client->updateDynamicSearchRule(
            self::SEARCH_RULE_UID,
            self::SEARCH_RULE_PATCH
        );

        $response = $this->client->getDynamicSearchRules([
            'offset' => 0,
            'limit' => 20,
            'filter' => ['attributePatterns' => [self::SEARCH_RULE_UID]],
        ]);

        self::assertCount(1, $response['results']);
        self::assertSame(self::SEARCH_RULE_UID, $response['results'][0]['uid']);
    }

    public function testCanCreateOrUpdateDynamicSearchRule(): void
    {
        $response = $this->client->updateDynamicSearchRule(
            self::SEARCH_RULE_UID,
            self::SEARCH_RULE_PATCH
        );

        self::assertSame(self::SEARCH_RULE_UID, $response['uid']);
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response['actions']);
    }

    public function testCanFetchDynamicSearchRule(): void
    {
        $this->client->updateDynamicSearchRule(
            self::SEARCH_RULE_UID,
            self::SEARCH_RULE_PATCH
        );

        $response = $this->client->getDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertSame(self::SEARCH_RULE_UID, $response['uid']);
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response['actions']);
    }

    public function testCanDeleteDynamicSearchRule(): void
    {
        $this->client->updateDynamicSearchRule(
            self::SEARCH_RULE_UID,
            self::SEARCH_RULE_PATCH
        );

        $response = $this->client->deleteDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertNull($response);
    }
}
