<?php namespace Maer\FileDB\Storage;

abstract class AbstractStorage
{
    /**
     * Load table data
     *
     * @param  string $table
     * @return array  $data
     */
    abstract public function loadTable($table);

    /**
     * Save table data
     *
     * @param  string $table
     * @param  array  $data
     * @return boolean
     */
    abstract public function saveTable($table, array &$data);

    /**
     * Get a blank table
     * @return array
     */
    protected function blankTable()
    {
        return [
            'meta' => ['created' => time(), 'modified' => time()],
            'data' => []
        ];
    }

    protected function touchModified(array &$data)
    {
        $data['meta']['modified'] = time();
        return $data;
    }
}
