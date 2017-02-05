<?php namespace Maer\FileDB;

use Exception;
use Maer\FileDB\Storage\AbstractStorage;

class FileDB
{
    /**
     * @var AbstractStorage
     */
    protected $storage;

    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @var Filters
     */
    protected $filters;


    /**
     * @param string $path
     */
    public function __construct($storage)
    {
        if (is_string($storage)) {
            $this->storage = new Storage\FileSystem($storage);
        } else if ($storage instanceof Storage\AbstractStorage) {
            $this->storage = $storage;
        } else {
            throw new Exception('Invalid storage. Must be a file path or an instance of AbstractStorage');
        }

        $this->filters = new Filters;
    }

    /**
     * Get a new query builder for the table
     *
     * @param  string $table
     * @return QueryBuilder
     */
    public function table($table)
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = new Table($table, $this->storage);
        }

        return new QueryBuilder($this->tables[$table], $this->filters);
    }

    /**
     * @param  string $table
     * @return QueryBuilder
     */
    public function __get($table)
    {
        return $this->table($table);
    }
}
