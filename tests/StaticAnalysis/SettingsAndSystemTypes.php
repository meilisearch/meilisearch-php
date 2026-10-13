<?php

declare(strict_types=1);

namespace Tests\StaticAnalysis;

use Meilisearch\Client;
use Meilisearch\Contracts\Index\Settings;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Index;

/**
 * @phpstan-import-type SettingsEmbedders from Settings
 * @phpstan-import-type SettingsEmbeddersUpdate from Settings
 */
final class SettingsAndSystemTypes
{
    public function separatorTokens(Index $index): int
    {
        return $this->expectSeparatorTokens($index->getSeparatorTokens())
            // @phpstan-ignore argument.type
            + $this->rejectStringKeyedTokens($index->getSeparatorTokens());
    }

    public function embedderShapes(): int
    {
        $userProvided = [
            'manual' => [
                'source' => 'userProvided',
                'dimensions' => 3,
                'binaryQuantized' => true,
                'headers' => ['X-Test' => '1'],
            ],
        ];
        $composite = [
            'embedder_name' => [
                'source' => 'composite',
                'searchEmbedder' => [
                    'source' => 'huggingFace',
                    'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                ],
                'indexingEmbedder' => [
                    'source' => 'huggingFace',
                    'model' => 'sentence-transformers/all-MiniLM-L6-v2',
                    'documentTemplate' => '{{doc.title}}',
                ],
            ],
        ];

        return $this->expectReadEmbedders($userProvided)
            + $this->expectReadEmbedders($composite)
            + $this->expectEmbedderUpdate($userProvided)
            + $this->expectEmbedderUpdate($composite)
            + $this->expectEmbedderUpdate(['old' => null])
            + $this->expectEmbedderUpdate(['manual' => ['source' => 'openAi', 'apiKey' => null]])
            // @phpstan-ignore argument.type
            + $this->expectReadEmbedders(['old' => null])
            // @phpstan-ignore argument.type
            + $this->expectReadEmbedders(['manual' => ['apiKey' => null]]);
    }

    public function embedderRead(Index $index): int
    {
        $embedders = $index->getEmbedders();
        if (null === $embedders) {
            return 0;
        }

        return $this->expectReadEmbedders($embedders)
            // @phpstan-ignore argument.type
            + $this->rejectIntegerKeyedEmbedders($embedders);
    }

    public function embedderWrite(Index $index): int
    {
        $userProvided = [
            'manual' => [
                'source' => 'userProvided',
                'dimensions' => 3,
                'binaryQuantized' => true,
            ],
        ];
        $written = $this->wrote($index->updateEmbedders($userProvided))
            + $this->wrote($index->updateEmbedders(['old' => null]))
            + $this->wrote($index->updateEmbedders(['manual' => ['source' => 'openAi', 'apiKey' => null]]));

        $embedders = $index->getEmbedders();
        if (null !== $embedders) {
            $written += $this->wrote($index->updateEmbedders($embedders));
        }

        // @phpstan-ignore argument.type
        $written += $this->wrote($index->updateEmbedders(['old' => 1]));

        return $written;
    }

    public function tenantToken(Client $client): int
    {
        return \strlen($client->generateTenantToken('uid', ['*']))
            + \strlen($client->generateTenantToken('uid', (object) ['*' => (object) []]))
            // @phpstan-ignore argument.type
            + \strlen($client->generateTenantToken('uid', 'bad'));
    }

    public function swapIndexes(Client $client): int
    {
        $written = $this->wrote($client->swapIndexes([['indexA', 'indexB'], ['indexC', 'indexD']]));

        // @phpstan-ignore argument.type
        $written += $this->wrote($client->swapIndexes([['indexA']]));

        return $written;
    }

    /**
     * @param list<non-empty-string> $tokens
     */
    private function expectSeparatorTokens(array $tokens): int
    {
        return \count($tokens);
    }

    /**
     * @param array<string, string> $tokens
     */
    private function rejectStringKeyedTokens(array $tokens): int
    {
        return \count($tokens);
    }

    /**
     * @param SettingsEmbedders $embedders
     */
    private function expectReadEmbedders(array $embedders): int
    {
        return \count($embedders);
    }

    /**
     * @param SettingsEmbeddersUpdate $embedders
     */
    private function expectEmbedderUpdate(array $embedders): int
    {
        return \count($embedders);
    }

    /**
     * @param array<int, int> $embedders
     */
    private function rejectIntegerKeyedEmbedders(array $embedders): int
    {
        return \count($embedders);
    }

    private function wrote(Task $task): int
    {
        return \count([$task->getStatus()]);
    }
}
