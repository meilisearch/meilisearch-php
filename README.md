<p align="center">
  <img src="https://res.cloudinary.com/meilisearch/image/upload/v1587402338/SDKs/meilisearch_php.svg" alt="MeiliSearch-PHP" width="200" height="200" />
</p>

<h1 align="center">MeiliSearch PHP</h1>

<h4 align="center">
  <a href="https://github.com/meilisearch/MeiliSearch">MeiliSearch</a> |
  <a href="https://www.meilisearch.com">Website</a> |
  <a href="https://blog.meilisearch.com">Blog</a> |
  <a href="https://twitter.com/meilisearch">Twitter</a> |
  <a href="https://docs.meilisearch.com">Documentation</a> |
  <a href="https://docs.meilisearch.com/resources/faq.html">FAQ</a>
</h4>

<p align="center">
  <a href="https://packagist.org/packages/meilisearch/meilisearch-php"><img src="https://img.shields.io/packagist/v/meilisearch/meilisearch-php" alt="Latest Stable Version"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/actions"><img src="https://github.com/meilisearch/meilisearch-php/workflows/Tests/badge.svg" alt="Test"></a>
  <a href="https://github.com/meilisearch/meilisearch-php/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-informational" alt="License"></a>
  <a href="https://slack.meilisearch.com"><img src="https://img.shields.io/badge/slack-MeiliSearch-blue.svg?logo=slack" alt="Slack"></a>
</p>

<p align="center">⚡ Lightning Fast, Ultra Relevant, and Typo-Tolerant Search Engine MeiliSearch client written in PHP</p>

**MeiliSearch PHP** is a client for **MeiliSearch** written in PHP. **MeiliSearch** is a powerful, fast, open-source, easy to use and deploy search engine. Both searching and indexing are highly customizable. Features such as typo-tolerance, filters, and synonyms are provided out-of-the-box.

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
    ['book_id' => 123,  'title' => 'Pride and Prejudice', 'author' => 'Jane Austen'],
    ['book_id' => 456,  'title' => 'Le Petit Prince', 'author' => 'Antoine de Saint-Exupéry'],
    ['book_id' => 1,    'title' => 'Alice In Wonderland', 'author' => 'Lewis Carroll'],
    ['book_id' => 1344, 'title' => 'The Hobbit', 'author' => 'J. R. R. Tolkien'],
    ['book_id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'author' => 'J. K. Rowling'],
    ['book_id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy', 'author' => 'Douglas Adams, Eoin Colfer, Thomas Tidholm'],
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

```php
$index->search('prince', ['limit' => 1]);
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

With limit and filter, both single and double quotes are supported.
```php
// Enclosing with double quotes
$index->search('prince', ['limit' => 2, 'filters' => "title = 'Le Petit Prince' OR author = 'J. R. R. Tolkien'"]);

// Enclosing with single quotes
$index->search('hobbit', ['limit' => 2, 'filters' => 'title = "The Hitchhiker\'s Guide to the Galaxy" OR author = "J. R. R. Tolkien"']);
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
$ sh scripts/tests.sh
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
