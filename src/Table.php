<?php namespace Maer\FileDB;

use Maer\FileDB\Storage\AbstractStorage;

class Table
{
    /**
     * @var data
     */
    public $data;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $file;


    /**
     * @param AbstractStorage $storage
     * @param string $table
     */
    public function __construct($table, AbstractStorage $storage)
    {
        $this->storage = $storage;
        $this->table   = $table;
        $this->data    = $this->storage->loadTable($table);
    }

    /**
     * Save the data
     * @return boolean
     */
    public function save()
    {
        return $this->storage->saveTable($this->table, $this->data);
    }
}
