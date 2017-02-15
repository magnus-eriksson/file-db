<?php namespace Maer\FileDB\Storage;

use Exception;

class FileSystem extends AbstractStorage
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $opt = [
        'json_options'  => 0,
        'json_depth'    => 512,
    ];


    /**
     * @param string $path
     * @param array  $options
     */
    public function __construct($path, array $options = [])
    {
        $this->path     = $path;
        $this->opt = array_merge($this->opt, $options);
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
            $data = file_get_contents($file);
            if (!$data) {
                return $this->blankTable();
            }

            if (!$data = @json_decode($data, true, 512)) {
                throw new Exception("Unable to parse table file '{$table}.json'");
            }

            return  $data;
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

        $opt  = is_numeric($this->opt['json_options'])
            ? $this->opt['json_options']
            : 0;

        $dept = is_numeric($this->opt['json_depth'])
            ? $this->opt['json_depth']
            : 512;

        return file_put_contents($file, json_encode($data, $opt, $dept), LOCK_EX) > 0;
    }
}
