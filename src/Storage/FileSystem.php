<?php namespace Maer\FileDB\Storage;

class FileSystem extends AbstractStorage
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Load table data
     *
     * @param  string $table
     * @return array  $data
     */
    public function loadTable($table)
    {
        $file = $this->path . '/' . $table . '.json';
        if (is_file($file)) {
            return json_decode(file_get_contents($file), true, 512);
        }

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
        $file = $this->path . '/' . $table . '.json';
        $data = $this->touchModified($data);

        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) > 0;
    }
}
