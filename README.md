<p align="center">
  <img src="https://raw.githubusercontent.com/meilisearch/integration-guides/main/assets/logos/meilisearch_php.svg" alt="Meilisearch-PHP" width="200" height="200" />
</p>

<h1 align="center">Meilisearch PHP</h1>

<h4 align="center">
  <a href="https://github.com/meilisearch/meilisearch">Meilisearch</a> |
  <a href="https://docs.meilisearch.com">Documentation</a> |
  <a href="https://slack.meilisearch.com">Slack</a> |
  <a href="https://roadmap.meilisearch.com/tabs/1-under-consideration">Roadmap</a> |
  <a href="https://www.meilisearch.com">Website</a> |
  <a href="https://docs.meilisearch.com/faq">FAQ</a>
</h4>

<p align="center">
  <a href="https://packagist.org/packages/meilisearch/meilisearch-php"><img src="https://img.shields.io/packagist/v/meilisearch/meilisearch-php" alt="Latest Stable Version"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/actions"><img src="https://github.com/meilisearch/meilisearch-php/workflows/Tests/badge.svg" alt="Test"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-informational" alt="License"></a>
  <a href="https://app.bors.tech/repositories/28780"><img src="https://bors.tech/images/badge_small.svg" alt="Bors enabled"></a>
</p>

<p align="center">⚡ The Meilisearch API client written for PHP 🐘</p>

**Meilisearch PHP** is the Meilisearch API client for PHP developers.

**Meilisearch** is an open-source search engine. [Discover what Meilisearch is!](https://github.com/meilisearch/Meilisearch)

## Table of Contents <!-- omit in toc -->

- [📖 Documentation](#-documentation)
- [🔧 Installation](#-installation)
- [🚀 Getting Started](#-getting-started)
- [🤖 Compatibility with Meilisearch](#-compatibility-with-meilisearch)
- [💡 Learn More](#-learn-more)
- [🧰 HTTP Client Compatibilities](#-http-client-compatibilities)
  - [Customize your HTTP Client](#customize-your-http-client)
- [⚙️ Development Workflow and Contributing](#️-development-workflow-and-contributing)

## 📖 Documentation

See our [Documentation](https://docs.meilisearch.com/learn/tutorials/getting_started.html) or our [API References](https://docs.meilisearch.com/reference/api/).

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

There are many easy ways to [download and run a Meilisearch instance](https://docs.meilisearch.com/reference/features/installation.html#download-and-launch).

For example, using the `curl` command in your [Terminal](https://itconnect.uw.edu/learn/workshops/online-tutorials/web-publishing/what-is-a-terminal/):

```sh
#Install Meilisearch
curl -L https://install.meilisearch.com | sh

# Launch Meilisearch
./meilisearch --master-key=masterKey
```

NB: you can also download Meilisearch from **Homebrew** or **APT** or even run it using **Docker**.

## 🚀 Getting Started

#### Add documents <!-- omit in toc -->

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MeiliSearch\Client;

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

With the `uid`, you can check the status (`enqueued`, `processing`, `succeeded` or `failed`) of your documents addition using the [task](https://docs.meilisearch.com/reference/api/tasks.html#get-task).

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

All the supported options are described in the [search parameters](https://docs.meilisearch.com/reference/features/search_parameters.html) section of the documentation.

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

Note that Meilisearch will rebuild your index whenever you update `filterableAttributes`. Depending on the size of your dataset, this might take time. You can track the process using the [tasks](https://docs.meilisearch.com/reference/api/tasks.html#get-task)).

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

This package only guarantees the compatibility with the [version v0.28.0 of Meilisearch](https://github.com/meilisearch/meilisearch/releases/tag/v0.28.0).

## 💡 Learn More

The following sections may interest you:

- **Manipulate documents**: see the [API references](https://docs.meilisearch.com/reference/api/documents.html) or read more about [documents](https://docs.meilisearch.com/learn/core_concepts/documents.html).
- **Search**: see the [API references](https://docs.meilisearch.com/reference/api/search.html) or follow our guide on [search parameters](https://docs.meilisearch.com/reference/features/search_parameters.html).
- **Manage the indexes**: see the [API references](https://docs.meilisearch.com/reference/api/indexes.html) or read more about [indexes](https://docs.meilisearch.com/learn/core_concepts/indexes.html).
- **Configure the index settings**: see the [API references](https://docs.meilisearch.com/reference/api/settings.html) or follow our guide on [settings parameters](https://docs.meilisearch.com/reference/features/settings.html).

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

Following the example in the [Getting Started](#-getting-started) section, with the Guzzle HTTP client:

```php
new Client('http://127.0.0.1:7700', 'masterKey', new GuzzleHttpClient(['timeout' => 2]));
```

## ⚙️ Development Workflow and Contributing

Any new contribution is more than welcome in this project!

If you want to know more about the development workflow or want to contribute, please visit our [contributing guidelines](/CONTRIBUTING.md) for detailed instructions!

<hr>

**Meilisearch** provides and maintains many **SDKs and Integration tools** like this one. We want to provide everyone with an **amazing search experience for any kind of project**. If you want to contribute, make suggestions, or just know what's going on right now, visit us in the [integration-guides](https://github.com/meilisearch/integration-guides) repository.
