<?php

function deleteAllIndexes($client)
{
    $indexes_array = $client->getAllIndexes();
    foreach ($indexes_array as $index) {
        $client->deleteIndex($index['uid']);
    }
}
