<?php namespace Maer\FileDB\Storage;

class Memory extends AbstractStorage
{
    /**
     * Load table data
     *
     * @param  string $table
     * @return array  $data
     */
    public function loadTable($table)
    {
        return $this->blankTable();
    }

    /**
     * Save table data
     *
     * @param  string $table
     * @param  array  $data
     * @return boolean
     */
    public function saveTable($table, array &$data)
    {
        $this->touchModified($data);
    }
}
