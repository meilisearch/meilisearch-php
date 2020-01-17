# MeiliSearch PHP Client <!-- omit in toc -->

[![Licence](https://img.shields.io/badge/licence-MIT-blue.svg)](https://img.shields.io/badge/licence-MIT-blue.svg)
[![Actions Status](https://github.com/meilisearch/meilisearch-php/workflows/Tests/badge.svg)](https://github.com/meilisearch/meilisearch-php/actions)
[![Latest Stable Version](https://poser.pugx.org/meilisearch/meilisearch-php/version)](https://packagist.org/packages/meilisearch/meilisearch-php)

The PHP client for MeiliSearch API.

MeiliSearch provides an ultra relevant and instant full-text search. Our solution is open-source and you can check out [our repository here](https://github.com/meilisearch/MeiliDB).</br>

Here is the [MeiliSearch documentation](https://docs.meilisearch.com/) 📖

## Table of Contents <!-- omit in toc -->

- [🔧 Installation](#-installation)
- [🚀 Getting started](#-getting-started)
- [🎬 Examples](#-examples)
  - [Indexes](#indexes)
  - [Documents](#documents)
  - [Update status](#update-status)
  - [Search](#search)
- [🤖 Compatibility with MeiliSearch](#-compatibility-with-meilisearch)

## 🔧 Installation

With composer:

```bash
$ composer require meilisearch/meilisearch-php
```

### Run MeiliSearch <!-- omit in toc -->

There are many ways to run a MeiliSearch instance.
All of them are detailed in the [documentation](https://docs.meilisearch.com/advanced_guides/binary.html).

For example, if you use Docker:
```bash
$ docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest --api-key=apiKey
```

## 🚀 Getting started

#### Add documents <!-- omit in toc -->

```php
use MeiliSearch\Client;

$client = new Client('http://localhost:7700', 'apiKey');
$index = $client->createIndex('Books', 'booksUid'); // If your index does not exist
$index = $client->getIndex('booksUid');             // If you already created your index

$documents = [
    ['id' => 123,  'title' => 'Pride and Prejudice'],
    ['id' => 456,  'title' => 'Le Petit Prince'],
    ['id' => 1,    'title' => 'Alice In Wonderland'],
    ['id' => 1344, 'title' => 'The Hobbit'],
    ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince'],
    ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
];

$index->addOrReplaceDocuments($documents); // => { "updateId": 1 }
```

With the `updateId`, you can check the status of your documents addition thanks to this [method](https://github.com/meilisearch/meilisearch-php#update-status).


#### Search in index <!-- omit in toc -->
```php
// MeiliSearch is typo-tolerant:
print_r($index->search('hary pottre'));
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
    [query] => hary pottre
)
```

## 🎬 Examples

All HTTP routes of MeiliSearch are accessible via methods in this SDK.</br>
You can check out [the API documentation](https://docs.meilisearch.com/references/).

### Indexes

#### Create an index <!-- omit in toc -->
```php
// Create an index
$index = $client->createIndex('Books');
// Create an index with a specific uid (uid must be unique)
$index = $client->createIndex('Books', 'booksUid');
```

#### List all indexes <!-- omit in toc -->
```php
$client->getAllIndexes();
```

#### Get an index object <!-- omit in toc -->
```php
$client->getIndex('booksUid');
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
$index->addOrReplaceDocuments([['id' => 2, 'title' => 'Madame Bovary']])
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
            "id": 456,
            "title": "Le Petit Prince"
        },
        {
            "id": 4,
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
            "id": 456,
            "title": "Le Petit Prince"
        }
    ],
    "offset": 0,
    "limit": 1,
    "processingTimeMs": 10,
    "query": "prince"
}
```

## 🤖 Compatibility with MeiliSearch

This package works for MeiliSearch `v0.8.x`.
