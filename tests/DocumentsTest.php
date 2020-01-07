<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

require_once 'utils.php';

class DocumentsTest extends TestCase
{
    private static $index;
    private static $client;
    private static $documents;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'apiKey');
        deleteAllIndexes(static::$client);
        static::$index = static::$client->createIndex('Name');
        static::$documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testAddDocuments()
    {
        $res = static::$index->addOrReplaceDocuments(static::$documents);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
    }

    // DOCUMENTS

    public function testGetDocuments()
    {
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents), $res);
    }

    public function testGetDocument()
    {
        $doc = $this->findDocumentWithId(static::$documents, 4);
        $res = static::$index->getDocument($doc['id']);
        $this->assertIsArray($res);
        $this->assertSame($res['id'], $doc['id']);
        $this->assertSame($res['title'], $doc['title']);
    }

    public function testReplaceDocuments()
    {
        $id = 2;
        $new_title = 'The Red And The Black';
        $res = static::$index->addOrReplaceDocuments([['id' => $id, 'title' => $new_title]]);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocument($id);
        $this->assertSame($res['id'], $id);
        $this->assertSame($res['title'], $new_title);
        $this->assertFalse(array_search('comment', $res));
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents), $res);
    }

    public function testUpdateDocuments()
    {
        $id = 456;
        $new_title = 'The Little Prince';
        $res = static::$index->addOrUpdateDocuments([['id' => $id, 'title' => $new_title]]);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocument($id);
        $this->assertSame($res['id'], $id);
        $this->assertSame($res['title'], $new_title);
        $this->assertArrayHasKey('comment', $res);
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents), $res);
    }

    public function testAddOrUpdateDocuments()
    {
        $id = 9;
        $title = '1984';
        $res = static::$index->addOrUpdateDocuments([['id' => $id, 'title' => $title]]);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocument($id);
        $this->assertSame($res['id'], $id);
        $this->assertSame($res['title'], $title);
        $this->assertFalse(array_search('comment', $res));
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents) + 1, $res);
    }

    public function testDeleteDocument()
    {
        $id = 9;
        $res = static::$index->deleteDocument($id);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents), $res);
        $this->assertNull($this->findDocumentWithId($res, $id));
    }

    public function testDeleteDocuments()
    {
        $ids = [1, 2];
        $res = static::$index->deleteDocuments($ids);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocuments();
        $this->assertCount(count(static::$documents) - 2, $res);
        $this->assertNull($this->findDocumentWithId($res, $ids[0]));
        $this->assertNull($this->findDocumentWithId($res, $ids[1]));
    }

    public function testDeleteAllDocuments()
    {
        $res = static::$index->deleteAllDocuments();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        usleep(10 * 1000);
        $res = static::$index->getDocuments();
        $this->assertCount(0, $res);
    }

    public function testExceptionIfNoDocumentIdWhenGetting()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->getDocument(1);
    }

    private function findDocumentWithId($documents, $id)
    {
        foreach ($documents as $document) {
            if ($document['id'] == $id) {
                return $document;
            }
        }
    }
}
