# MeiliSearch PHP Client <!-- omit in toc -->

[![Licence](https://img.shields.io/badge/licence-MIT-blue.svg)](https://img.shields.io/badge/licence-MIT-blue.svg)
[![Actions Status](https://github.com/meilisearch/meilisearch-php/workflows/Tests/badge.svg)](https://github.com/meilisearch/meilisearch-php/actions)
[![Latest Stable Version](https://poser.pugx.org/meilisearch/meilisearch-php/version)](https://packagist.org/packages/meilisearch/meilisearch-php)

The PHP client for MeiliSearch API.

MeiliSearch provides an ultra relevant and instant full-text search. Our solution is open-source and you can check out [our repository here](https://github.com/meilisearch/MeiliSearch).</br>

Here is the [MeiliSearch documentation](https://docs.meilisearch.com/) 📖

## Table of Contents <!-- omit in toc -->

- [🔧 Installation](#-installation)
- [🚀 Getting started](#-getting-started)
- [🎬 Examples](#-examples)
  - [Indexes](#indexes)
  - [Documents](#documents)
  - [Update status](#update-status)
  - [Search](#search)
- [⚙️ Development Workflow](#️-development-workflow)
  - [Install dependencies](#install-dependencies)
  - [Tests and Linter](#tests-and-linter)
  - [Release](#release)
- [🤖 Compatibility with MeiliSearch](#-compatibility-with-meilisearch)

## 🔧 Installation

With composer:

```bash
$ composer require meilisearch/meilisearch-php
```

### Run MeiliSearch <!-- omit in toc -->

There are many easy ways to [download and run a MeiliSearch instance](https://docs.meilisearch.com/guides/advanced_guides/installation.html#download-and-launch).

For example, if you use Docker:
```bash
$ docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest --master-key=masterKey
```

NB: you can also download MeiliSearch from **Homebrew** or **APT**.

## 🚀 Getting started

#### Add documents <!-- omit in toc -->

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MeiliSearch\Client;

$client = new Client('http://127.0.0.1:7700', 'masterKey');
$index = $client->createIndex('books'); // If your index does not exist
$index = $client->getIndex('books');    // If you already created your index

$documents = [
    ['book_id' => 123,  'title' => 'Pride and Prejudice'],
    ['book_id' => 456,  'title' => 'Le Petit Prince'],
    ['book_id' => 1,    'title' => 'Alice In Wonderland'],
    ['book_id' => 1344, 'title' => 'The Hobbit'],
    ['book_id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince'],
    ['book_id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
];

$index->addDocuments($documents); // => { "updateId": 0 }
```

With the `updateId`, you can check the status (`processed` or `failed`) of your documents addition thanks to this [method](#update-status).


#### Search in index <!-- omit in toc -->

```php
// MeiliSearch is typo-tolerant:
print_r($index->search('harry pottre'));
```
Output:
```php
Array
(
    [hits] => Array
        (
            [0] => Array
                (
                    [id] => 4
                    [title] => Harry Potter and the Half-Blood Prince
                )

        )

    [offset] => 0
    [limit] => 20
    [processingTimeMs] => 1
    [query] => harry pottre
)
```

## 🎬 Examples

All HTTP routes of MeiliSearch are accessible via methods in this SDK.</br>
You can check out [the API documentation](https://docs.meilisearch.com/references/).

### Indexes

#### Create an index <!-- omit in toc -->

```php
// Create an index
$index = $client->createIndex('books');
// Create an index and give the primary-key
$index = $client->createIndex([
    'uid' => 'books',
    'primaryKey' => 'book_id'
]);
```

#### List all indexes <!-- omit in toc -->

```php
$client->getAllIndexes();
```

#### Get an index object <!-- omit in toc -->

```php
$client->getIndex('books');
```

### Documents

#### Fetch documents <!-- omit in toc -->

```php
// Get one document
$index->getDocument(123);
// Get documents by batch
$index->getDocuments(['offset' => 10 , 'limit' => 20]);
```

#### Add documents <!-- omit in toc -->

```php
$index->addDocuments([['book_id' => 2, 'title' => 'Madame Bovary']])
```

Response:
```json
{
    "updateId": 1
}
```
With this `updateId` you can track your [operation update](#update-status).

#### Delete documents <!-- omit in toc -->

```php
// Delete one document
$index->deleteDocument(2);
// Delete several documents
$index->deleteDocuments([1, 42]);
// Delete all documents /!\
$index->deleteAllDocuments();
```

### Update status

```php
// Get one update status
// Parameter: the updateId got after an asynchronous request (e.g. documents addition)
$index->getUpdateStatus(1);
// Get all update satus
$index->getAllUpdateStatus();
```

### Search

#### Basic search <!-- omit in toc -->

```php
$index->search('prince');
```

```json
{
    "hits": [
        {
            "book_id": 456,
            "title": "Le Petit Prince"
        },
        {
            "book_id": 4,
            "title": "Harry Potter and the Half-Blood Prince"
        }
    ],
    "offset": 0,
    "limit": 20,
    "processingTimeMs": 13,
    "query": "prince"
}
```

#### Custom search <!-- omit in toc -->

All the supported options are described in [this documentation section](https://docs.meilisearch.com/references/search.html#search-in-an-index).

```ruby
$index->search('prince', ['limit' => 1])
```

```json
{
    "hits": [
        {
            "book_id": 456,
            "title": "Le Petit Prince"
        }
    ],
    "offset": 0,
    "limit": 1,
    "processingTimeMs": 10,
    "query": "prince"
}
```

## ⚙️ Development Workflow

If you want to contribute, this section describes the steps to follow.

Thank you for your interest in a MeiliSearch tool! ♥️

### Install dependencies

```bash
$ composer install
```

### Tests and Linter

Each PR should pass the tests and the linter to be accepted.

```bash
# Tests
$ docker run -d -p 7700:7700 getmeili/meilisearch:latest ./meilisearch --master-key=masterKey --no-analytics=true
$ vendor/bin/phpunit --color tests/
# Linter (with auto-fix)
$ vendor/bin/php-cs-fixer fix --verbose --config=.php_cs.dist
# Linter (without auto-fix)
$ vendor/bin/php-cs-fixer fix --verbose --config=.php_cs.dist --dry-run
```

### Release

MeiliSearch tools follow the [Semantic Versioning Convention](https://semver.org/).

You must do a PR modifying the file `src/MeiliSearch.php` with the right version.<br>

```php
const VERSION = 'X.X.X';
```

Then, you must create a release (with this name `vX.X.X`) via the GitHub interface.<br>
A webhook will be triggered and push the new package on [Packagist](https://packagist.org/packages/meilisearch/meilisearch-php).

## 🤖 Compatibility with MeiliSearch

This package works for MeiliSearch `>=v0.10`.
