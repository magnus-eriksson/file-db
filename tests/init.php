<?php
require __DIR__ . '/../vendor/autoload.php';

use Maer\FileDB\FileDB;
use Maer\FileDB\Storage\Memory;

/**
 * Access the query builder instance easier
 * @param  string $table
 * @return QueryBuilder
 */
function db($table)
{
    static $db;
    if (is_null($db)) {
        $db = new FileDB(new Memory);
    }
    return $db->table($table);
}