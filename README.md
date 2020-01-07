# MeiliSearch PHP Client <!-- omit in toc -->

[![Licence](https://img.shields.io/badge/licence-MIT-blue.svg)](https://img.shields.io/badge/licence-MIT-blue.svg)

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

*WIP*

### Run MeiliSearch <!-- omit in toc -->

Here is a the [documentation](https://docs.meilisearch.com/advanced_guides/binary.html) to install and run Meilisearch.

## 🚀 Getting started

#### Add documents <!-- omit in toc -->

```php
use MeiliSearch\Client;

$client = new Client('http://localhost:7700', 'apiKey');
$index = $client->getIndex('indexUID');

$documents = [
    ['id' => 123,  'title' => 'Pride and Prejudice'],
    ['id' => 456,  'title' => 'Le Petit Prince'],
    ['id' => 1,    'title' => 'Alice In Wonderland'],
    ['id' => 1344, 'title' => 'The Hobbit'],
    ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince'],
    ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
];

$index->addOrReplaceDocuments($documents); // asynchronous
```

⚠️ The method `addOrReplaceDocuments` is **[asynchronous](https://docs.meilisearch.com/advanced_guides/asynchronous_updates.html)**.<br/>
It means that your new documents addition will not be taken into account if you do a request *right after* your addition in the same PHP script.

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

#### Create an index <!-- omit in toc -->

If you don't have any index yet, you can create one with:

```php
$index = $client->createIndex('Books');
echo $index->getUid();
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
$index = $client->createIndex('Books', 'booksUID');
```

#### List all indexes <!-- omit in toc -->
```php
$client->getAllIndexes();
```

#### Get an index object <!-- omit in toc -->
```php
$client->getIndex('indexUid');
```

### Documents

#### Fetch documents <!-- omit in toc -->
```php
# Get one document
$index->getDocument(123);
# Get documents by batch
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
# Get one update status
# Parameter: the updateId got after an asynchronous request (e.g. documents addition)
$index->getUpdateStatus(1);
# Get all update satus
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

This gem works for MeiliSearch `v0.8.x`.
