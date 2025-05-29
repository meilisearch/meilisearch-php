<p align="center">
  <img src="https://raw.githubusercontent.com/meilisearch/integration-guides/main/assets/logos/meilisearch_php.svg" alt="Meilisearch-PHP" width="200" height="200" />
</p>

<h1 align="center">Meilisearch PHP</h1>

<h4 align="center">
  <a href="https://github.com/meilisearch/meilisearch">Meilisearch</a> |
  <a href="https://www.meilisearch.com/cloud?utm_campaign=oss&utm_source=github&utm_medium=meilisearch-php">Meilisearch Cloud</a> |
  <a href="https://www.meilisearch.com/docs">Documentation</a> |
  <a href="https://discord.meilisearch.com">Discord</a> |
  <a href="https://roadmap.meilisearch.com/tabs/1-under-consideration">Roadmap</a> |
  <a href="https://www.meilisearch.com">Website</a> |
  <a href="https://www.meilisearch.com/docs/faq">FAQ</a>
</h4>

<p align="center">
  <a href="https://codecov.io/gh/meilisearch/meilisearch-php/branch/main"><img src="https://codecov.io/gh/meilisearch/meilisearch-php/branch/main/graph/badge.svg" alt="Codecov coverage"></a>
  <a href="https://packagist.org/packages/meilisearch/meilisearch-php"><img src="https://img.shields.io/packagist/v/meilisearch/meilisearch-php" alt="Latest Stable Version"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/actions"><img src="https://github.com/meilisearch/meilisearch-php/workflows/Tests/badge.svg" alt="Test"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-informational" alt="License"></a>
  <a href="https://ms-bors.herokuapp.com/repositories/59"><img src="https://bors.tech/images/badge_small.svg" alt="Bors enabled"></a>
</p>

<p align="center">⚡ The Meilisearch API client written for PHP 🐘</p>

**Meilisearch PHP** is the Meilisearch API client for PHP developers.

**Meilisearch** is an open-source search engine. [Learn more about Meilisearch.](https://github.com/meilisearch/Meilisearch)

## Table of Contents <!-- omit in TOC -->

- [📖 Documentation](#-documentation)
- [🔧 Installation](#-installation)
- [🚀 Getting started](#-getting-started)
- [🤖 Compatibility with Meilisearch](#-compatibility-with-meilisearch)
- [💡 Learn more](#-learn-more)
- [🧰 HTTP Client Compatibilities](#-http-client-compatibilities)
  - [Customize your HTTP Client](#customize-your-http-client)
- [⚙️ Contributing](#️-contributing)

## 📖 Documentation


To learn more about Meilisearch PHP, refer to the in-depth [Meilisearch PHP Documentation](https://php-sdk.meilisearch.com). To learn more about Meilisearch in general, refer to our [documentation](https://www.meilisearch.com/docs/learn/getting_started/quick_start) or our [API reference](https://www.meilisearch.com/docs/reference/api/overview).

## 🔧 Installation

To get started, simply require the project using [Composer](https://getcomposer.org/).<br>
You will also need to install packages that "provide" [`psr/http-client-implementation`](https://packagist.org/providers/psr/http-client-implementation) and [`psr/http-factory-implementation`](https://packagist.org/providers/psr/http-factory-implementation).<br>
A list with compatible HTTP clients and client adapters can be found at [php-http.org](http://docs.php-http.org/en/latest/clients.html).

**If you don't know which HTTP client to use, we recommend using Guzzle 7**:

```bash
composer require meilisearch/meilisearch-php guzzlehttp/guzzle http-interop/http-factory-guzzle:^1.0
```

Here is an example of installation with the `symfony/http-client`:

```bash
composer require meilisearch/meilisearch-php symfony/http-client nyholm/psr7:^1.0
```

💡 *More HTTP client installations compatible with this package can be found [in this section](#-http-client-compatibilities).*

### Run Meilisearch <!-- omit in toc -->

⚡️ **Launch, scale, and streamline in minutes with Meilisearch Cloud**—no maintenance, no commitment, cancel anytime. [Try it free now](https://cloud.meilisearch.com/login?utm_campaign=oss&utm_source=github&utm_medium=meilisearch-php).

🪨  Prefer to self-host? [Download and deploy](https://www.meilisearch.com/docs/learn/self_hosted/getting_started_with_self_hosted_meilisearch?utm_campaign=oss&utm_source=github&utm_medium=meilisearch-php) our fast, open-source search engine on your own infrastructure.

## 🚀 Getting started

#### Add documents <!-- omit in toc -->

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Meilisearch\Client;

$client = new Client('http://127.0.0.1:7700', 'masterKey');

# An index is where the documents are stored.
$index = $client->index('movies');

$documents = [
    ['id' => 1,  'title' => 'Carol', 'genres' => ['Romance, Drama']],
    ['id' => 2,  'title' => 'Wonder Woman', 'genres' => ['Action, Adventure']],
    ['id' => 3,  'title' => 'Life of Pi', 'genres' => ['Adventure, Drama']],
    ['id' => 4,  'title' => 'Mad Max: Fury Road', 'genres' => ['Adventure, Science Fiction']],
    ['id' => 5,  'title' => 'Moana', 'genres' => ['Fantasy, Action']],
    ['id' => 6,  'title' => 'Philadelphia', 'genres' => ['Drama']],
];

# If the index 'movies' does not exist, Meilisearch creates it when you first add the documents.
$index->addDocuments($documents); // => { "uid": 0 }
```

With the `uid`, you can check the status (`enqueued`, `canceled`, `processing`, `succeeded` or `failed`) of your documents addition using the [task](https://www.meilisearch.com/docs/reference/api/tasks#status).

#### Basic Search <!-- omit in toc -->

```php
// Meilisearch is typo-tolerant:
$hits = $index->search('wondre woman')->getHits();
print_r($hits);
```

Output:

```php
Array
(
    [0] => Array
        (
            [id] => 2
            [title] => Wonder Woman
            [genres] => Array
                (
                     [0] => Action, Adventure
                )
        )
)
```

#### Custom Search <!-- omit in toc -->

All the supported options are described in the [search parameters](https://www.meilisearch.com/docs/reference/api/search#search-parameters) section of the documentation.

💡 **More about the `search()` method in [the Wiki](https://github.com/meilisearch/meilisearch-php/wiki/Search).**

```php
$index->search(
    'phil',
    [
        'attributesToHighlight' => ['*'],
    ]
)->getRaw(); // Return in Array format
```

JSON output:

```json
{
    "hits": [
        {
            "id": 6,
            "title": "Philadelphia",
            "genre": ["Drama"],
            "_formatted": {
                "id": 6,
                "title": "<em>Phil</em>adelphia",
                "genre": ["Drama"]
            }
        }
    ],
    "offset": 0,
    "limit": 20,
    "processingTimeMs": 0,
    "query": "phil"
}
```
#### Custom Search With Filters <!-- omit in toc -->

If you want to enable filtering, you must add your attributes to the `filterableAttributes` index setting.

```php
$index->updateFilterableAttributes([
  'id',
  'genres'
]);
```

You only need to perform this operation once.

Note that Meilisearch will rebuild your index whenever you update `filterableAttributes`. Depending on the size of your dataset, this might take time. You can track the process using the [tasks](https://www.meilisearch.com/docs/reference/api/tasks#get-tasks)).

Then, you can perform the search:

```php
$index->search(
  'wonder',
  [
    'filter' => ['id > 1 AND genres = Action']
  ]
);
```

```json
{
  "hits": [
    {
      "id": 2,
      "title": "Wonder Woman",
      "genres": ["Action","Adventure"]
    }
  ],
  "offset": 0,
  "limit": 20,
  "estimatedTotalHits": 1,
  "processingTimeMs": 0,
  "query": "wonder"
}
```

## 🤖 Compatibility with Meilisearch

This package guarantees compatibility with [version v1.x of Meilisearch](https://github.com/meilisearch/meilisearch/releases/latest), but some features may not be present. Please check the [issues](https://github.com/meilisearch/meilisearch-php/issues?q=is%3Aissue+is%3Aopen+label%3A%22good+first+issue%22+label%3Aenhancement) for more info.

## 💡 Learn more

The following sections in our main documentation website may interest you:

- **Manipulate documents**: see the [API references](https://www.meilisearch.com/docs/reference/api/documents) or read more about [documents](https://www.meilisearch.com/docs/learn/core_concepts/documents).
- **Search**: see the [API references](https://www.meilisearch.com/docs/reference/api/search) or follow our guide on [search parameters](https://www.meilisearch.com/docs/reference/api/search#search-parameters).
- **Manage the indexes**: see the [API references](https://www.meilisearch.com/docs/reference/api/indexes) or read more about [indexes](https://www.meilisearch.com/docs/learn/core_concepts/indexes).
- **Configure the index settings**: see the [API references](https://www.meilisearch.com/docs/reference/api/settings) or follow our guide on [settings parameters](https://www.meilisearch.com/docs/learn/configuration/settings).

## 🧰 HTTP Client Compatibilities

You could use any [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible client to use with this SDK. No additional configurations are required.<br>
A list of compatible HTTP clients and client adapters can be found at [php-http.org](http://docs.php-http.org/en/latest/clients.html).

If you want to use this `meilisearch-php`:

- with `guzzlehttp/guzzle` (Guzzle 7), run:

```bash
composer require meilisearch/meilisearch-php guzzlehttp/guzzle http-interop/http-factory-guzzle:^1.0
```

- with `php-http/guzzle6-adapter` (Guzzle < 7), run:

```bash
composer require meilisearch/meilisearch-php php-http/guzzle6-adapter:^2.0 http-interop/http-factory-guzzle:^1.0
```

- with `symfony/http-client`, run:

```bash
composer require meilisearch/meilisearch-php symfony/http-client nyholm/psr7:^1.0
```

- with `php-http/curl-client`, run:

```bash
composer require meilisearch/meilisearch-php php-http/curl-client nyholm/psr7:^1.0
```

- with `kriswallsmith/buzz`, run:

```bash
composer require meilisearch/meilisearch-php kriswallsmith/buzz nyholm/psr7:^1.0
```

### Customize your HTTP Client

For some reason, you might want to pass a custom configuration to your own HTTP client.<br>
Make sure you have a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible client when you initialize the Meilisearch client.

Following the example in the [Getting started](#-getting-started) section, with the Guzzle HTTP client:

```php
new Client('http://127.0.0.1:7700', 'masterKey', new GuzzleHttpClient(['timeout' => 2]));
```

## ⚙️ Contributing

Any new contribution is more than welcome in this project!

If you want to know more about the development workflow or want to contribute, please visit our [contributing guidelines](/CONTRIBUTING.md) for detailed instructions!

<hr>

**Meilisearch** provides and maintains many **SDKs and Integration tools** like this one. We want to provide everyone with an **amazing search experience for any kind of project**. If you want to contribute, make suggestions, or just know what's going on right now, visit us in the [integration-guides](https://github.com/meilisearch/integration-guides) repository.
