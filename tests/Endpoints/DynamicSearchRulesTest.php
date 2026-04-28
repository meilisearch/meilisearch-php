<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\DynamicSearchRulesFilter;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class DynamicSearchRulesTest extends TestCase
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

        foreach ($response as $rule) {
            $this->client->deleteDynamicSearchRule($rule->getUid());
        }

        parent::tearDown();
    }

    public function testCanListDynamicSearchRules(): void
    {
        $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );

        $response = $this->client->getDynamicSearchRules(
            (new DynamicSearchRulesQuery())
                ->setOffset(0)
                ->setLimit(20)
                ->setFilter((new DynamicSearchRulesFilter())->setAttributePatterns([self::SEARCH_RULE_UID]))
        );

        self::assertCount(1, $response);
        self::assertSame(self::SEARCH_RULE_UID, $response->getResults()[0]->getUid());
    }

    public function testCanCreateOrUpdateDynamicSearchRule(): void
    {
        $response = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );

        self::assertSame(self::SEARCH_RULE_UID, $response->getUid());
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response->getActions());
    }

    public function testCanFetchDynamicSearchRule(): void
    {
        $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );

        $response = $this->client->getDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertSame(self::SEARCH_RULE_UID, $response->getUid());
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response->getActions());
    }

    public function testCanDeleteDynamicSearchRule(): void
    {
        $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );

        $response = $this->client->deleteDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertNull($response);
    }
}
