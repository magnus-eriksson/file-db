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
     * @var array
     */
    protected $opt = [];


    /**
     * @param string $path
     * @param array  $options
     */
    public function __construct($storage, array $options = [])
    {
        $this->opt = $options;

        if (!$storage instanceof Storage\AbstractStorage) {
            throw new Exception('Invalid storage. Must be a file path or an instance of AbstractStorage');
        }

        $this->storage = $storage;
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
