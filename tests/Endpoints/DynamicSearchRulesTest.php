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
    private const SEARCH_RULE_DESCRIPTION = 'Movie promotion rule';
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
        $task = $this->client->deleteAllDynamicSearchRules();
        $this->client->waitForTask($task['taskUid'], 5000, 50);

        parent::tearDown();
    }

    public function testCanListDynamicSearchRules(): void
    {
        $task = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))
                ->setDescription(self::SEARCH_RULE_DESCRIPTION)
                ->setActions(self::SEARCH_RULE_PATCH['actions'])
        );
        $this->client->waitForTask($task['taskUid'], 5000, 50);

        $response = $this->client->getDynamicSearchRules(
            (new DynamicSearchRulesQuery())
                ->setOffset(0)
                ->setLimit(20)
                ->setFilter((new DynamicSearchRulesFilter())->setQuery('Movie promotion'))
        );

        self::assertCount(1, $response);
        self::assertSame(self::SEARCH_RULE_UID, $response->getResults()[0]->getUid());
    }

    public function testCanCreateOrUpdateDynamicSearchRule(): void
    {
        $task = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );

        self::assertSame('enqueued', $task['status']);
        $this->client->waitForTask($task['taskUid'], 5000, 50);

        $response = $this->client->getDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertSame(self::SEARCH_RULE_UID, $response->getUid());
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response->getActions());
    }

    public function testCanFetchDynamicSearchRule(): void
    {
        $task = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))->setActions(self::SEARCH_RULE_PATCH['actions'])
        );
        $this->client->waitForTask($task['taskUid'], 5000, 50);

        $response = $this->client->getDynamicSearchRule(self::SEARCH_RULE_UID);

        self::assertSame(self::SEARCH_RULE_UID, $response->getUid());
        self::assertSame(self::SEARCH_RULE_PATCH['actions'], $response->getActions());
    }

    public function testCanDeleteDynamicSearchRule(): void
    {
        $createTask = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))
                ->setDescription(self::SEARCH_RULE_DESCRIPTION)
                ->setActions(self::SEARCH_RULE_PATCH['actions'])
        );
        $this->client->waitForTask($createTask['taskUid'], 5000, 50);

        $task = $this->client->deleteDynamicSearchRule(self::SEARCH_RULE_UID);

        $this->client->waitForTask($task['taskUid'], 5000, 50);

        $response = $this->client->getDynamicSearchRules(
            (new DynamicSearchRulesQuery())
                ->setFilter((new DynamicSearchRulesFilter())->setQuery('promotion'))
        );

        self::assertCount(0, $response);
    }

    public function testCanDeleteAllDynamicSearchRules(): void
    {
        $firstTask = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery(self::SEARCH_RULE_UID))
                ->setDescription(self::SEARCH_RULE_DESCRIPTION)
                ->setActions(self::SEARCH_RULE_PATCH['actions'])
        );
        $this->client->waitForTask($firstTask['taskUid'], 5000, 50);
        $secondTask = $this->client->updateDynamicSearchRule(
            (new UpdateDynamicSearchRuleQuery('promo-rule'))
                ->setDescription('Promo rule')
                ->setActions(self::SEARCH_RULE_PATCH['actions'])
        );
        $this->client->waitForTask($secondTask['taskUid'], 5000, 50);

        $task = $this->client->deleteAllDynamicSearchRules();

        self::assertSame('enqueued', $task['status']);
        self::assertSame('dsrClear', $task['type']);
        $this->client->waitForTask($task['taskUid'], 5000, 50);

        self::assertCount(0, $this->client->getDynamicSearchRules());
    }
}
