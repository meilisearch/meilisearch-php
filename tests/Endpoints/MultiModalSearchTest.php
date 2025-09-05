<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Http\Client;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class MultiModalSearchTest extends TestCase
{
    private Indexes $index;

    private function getEmbedderConfig(string $voyageApiKey): array
    {
        return [
            'source' => 'rest',
            'url' => 'https://api.voyageai.com/v1/multimodalembeddings',
            'apiKey' => $voyageApiKey,
            'dimensions' => 1024,
            'indexingFragments' => [
                'textAndPoster' => [
                    // the shape of the data here depends on the model used
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'A movie titled {{doc.title}} whose description starts with {{doc.overview|truncatewords:20}}.',
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => '{{doc.poster}}',
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'value' => [
                        // The shape of the data here depends on the model used
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'A movie titled {{doc.title}} whose description starts with {{doc.overview|truncatewords:20}}.',
                            ],
                        ],
                    ],
                ],
                'poster' => [
                    'value' => [
                        // The shape of the data here depends on the model used
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => '{{doc.poster}}',
                            ],
                        ],
                    ],
                ],
            ],
            'searchFragments' => [
                'textAndPoster' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => '{{media.textAndPoster.text}}',
                            ],
                            [
                                'type' => 'image_base64',
                                'image_base64' =>
                                    'data:{{media.textAndPoster.image.mime}};base64,{{media.textAndPoster.image.data}}',
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => '{{media.text.text}}',
                            ],
                        ],
                    ],
                ],
                'poster' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => '{{media.poster.poster}}',
                            ],
                        ],
                    ],
                ],
            ],
            'request' => [
                // This request object matches the Voyage API request object
                'inputs' => ['{{fragment}}', '{{..}}'],
                'model' => 'voyage-multimodal-3',
            ],
            'response' => [
                // This response object matches the Voyage API response object
                'data' => [
                    [
                        'embedding' => '{{embedding}}',
                    ],
                    '{{..}}',
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['multimodal' => true]);

        $voyageApiKey = getenv('VOYAGE_API_KEY');
        if (!$voyageApiKey) {
            throw new \Exception('Missing `VOYAGE_API_KEY` environment variable');
        }

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $this->index->updateSettings([
            'searchableAttributes' => ['title', 'overview'],
            'embedders' => [
                'multimodal' => $this->getEmbedderConfig($voyageApiKey),
            ],
        ]);

        // Load the movies.json dataset
        $fileJson = fopen('./tests/datasets/movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/movies.json'));
        fclose($fileJson);
        $this->index->addDocumentsJson($documentJson);
    }

    public function testTextOnlySearch(): void
    {
        self::markTestSkipped('MultiModalSearch is not implemented yet');
    }
}
