<?php namespace Maer\FileDB;

use Exception;

class QueryBuilder
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var Filters
     */
    protected $filters;

    /**
     * Return type
     * @var string
     */
    protected $returnAs = 'array';

    /**
     * @var array
     */
    protected $order = [null, 'asc'];

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $offset;


    /**
     * @param Table $table
     */
    public function __construct(Table $table, Filters $filters)
    {
        $this->table   = $table;
        $this->filters = $filters;
    }


    /**
     * Get a meta value
     *
     * @param  string $key
     * @return mixed
     */
    public function meta($key)
    {
        return $this->table->data['meta'][$key] ?? null;
    }


    /**
     * Insert a new record
     *
     * @param  array  $data
     * @return string $id
     */
    public function insert(array $data)
    {
        if (isset($data['id'])) {
            if (array_key_exists($data['id'], $this->table->data['data'])) {
                return null;
            }
        } else {
            $data = ['id' => $this->generateId()] + $data;
        }

        $this->table->data['data'][$data['id']] = $data;
        $this->table->save();

        return $data['id'];
    }


    /**
     * Batch insert multiple records
     *
     * @param  array $data List of records
     * @return array $ids
     */
    public function batchInsert(array $data)
    {
        $ids = [];
        foreach ($data as $item) {
            if (isset($item['id'])) {
                if (array_key_exists($item['id'], $this->table->data['data'])) {
                    continue;
                }
            } else {
                $item = ['id' => $this->generateId()] + $item;
            }

            $ids[] = $this->insert($item);
        }

        return $ids;
    }


    /**
     * Update records
     *
     * @param  array   $data
     * @return integer $affectedRows
     */
    public function update(array $data)
    {
        $affected = 0;
        foreach ($this->table->data['data'] as &$rs) {
            if ($this->matchWhere($rs)) {
                $rs = array_merge($rs, $data);
                $affected++;
            }
        }

        if ($affected) {
            $this->table->save();
        }

        return $affected;
    }


    /**
     * Replace records
     *
     * @param  array   $data
     * @return integer $affectedRows
     */
    public function replace(array $data)
    {
        $affected = 0;
        foreach ($this->table->data['data'] as &$rs) {
            if ($this->matchWhere($rs)) {
                $data['id'] = $rs['id'];
                $rs = $data;
                $affected++;
            }
        }

        if ($affected) {
            $this->table->save();
        }

        return $affected;
    }


    /**
     * Delete records
     *
     * @return integer $affectedRows
     */
    public function delete()
    {
        $affected = 0;
        foreach ($this->table->data['data'] as $rs) {
            if ($this->matchWhere($rs)) {
                unset($this->table->data['data'][$rs['id']]);
                $affected++;
            }
        }

        if ($affected) {
            $this->table->save();
        }

        return $affected;
    }


    /**
     * Truncate a table
     * @return boolean
     */
    public function truncate()
    {
        $this->table->data['data'] = [];
        return $this->table->save();
    }


    /**
     * Set sort column and order
     *
     * @param  string $column
     * @param  string $order    Either 'asc' or 'desc'
     * @return $this
     */
    public function orderBy($column, $order = 'asc')
    {
        $this->order[0] = $column;
        $this->order[1] = strtolower($order) == 'desc' ? 'desc' : 'asc';
        return $this;
    }


    /**
     * Return results as object
     *
     * @param  string $class
     * @return $this
     */
    public function asObj($class = 'stdClass')
    {
        $this->returnAs = strtolower($class) == 'stdclass' || strtolower($class) == 'array'
            ? strtolower($class)
            : '\\' . ltrim($class, '\\');

        return $this;
    }


    /**
     * Get records
     *
     * @return array
     */
    public function get()
    {
        $list      = [];
        $i         = 0;
        $realLimit = $this->offset && $this->limit
            ? $this->offset + $this->limit
            : $this->limit;

        foreach ($this->getData() as &$rs) {
            if ($this->offset && $this->offset > $i) {
                $i++;
                continue;
            }

            if ($realLimit && $realLimit == $i) {
                break;
            }

            if (!$this->where) {
                $list[] = $this->convertItem($rs);
                $i++;
                continue;
            }

            if ($this->matchWhere($rs)) {
                $list[] = $this->convertItem($rs);
                $i++;
            }
        }

        return $list;
    }


    /**
     * Get the number of results the current query returns
     *
     * @return integer
     */
    public function count()
    {
        return count($this->get());
    }


    /**
     * Get first record
     *
     * @return array
     */
    public function first()
    {
        $data = $this->getData();

        if (!$this->where) {
            return $data
                ? $this->convertItem(reset($data))
                : null;
        }

        foreach ($data as &$rs) {
            if ($this->matchWhere($rs)) {
                return $this->convertItem($rs);
            }
        }

        return null;
    }


    /**
     * Find a record (short for ->where('id', $id)->first())
     *
     * @param  string $id
     * @param  string $column
     * @return array
     */
    public function find($id, $column = 'id')
    {
        $this->where($column, $id);

        return $this->first();
    }


    /**
     * Limit the result
     *
     * @param  integer $limit
     * @return $this
     */
    public function limit($limit)
    {
        if (intval($limit) != $limit) {
            throw new \Exception('The limit must be an integer');
        }

        $this->limit = (int) $limit;

        return $this;
    }


    /**
     * Offset the result
     *
     * @param  integer $offset
     * @return $this
     */
    public function offset($offset)
    {
        if (intval($offset) != $offset) {
            throw new \Exception('The offset must be an integer');
        }

        $this->offset = (int) $offset;

        return $this;
    }


    /**
     * Value must exist in the array
     *
     * @param  string $column   Column to search
     * @param  string $operator Operator or value
     * @param  mixed  $value
     * @return $this
     */
    public function where($column, $operator, $value = null)
    {
        if (is_null($value)) {
            if (is_callable($operator)) {
                $this->where[] = [$column, 'func', $operator];
            } else {
                $this->where[] = [$column, '=', $operator];
            }
        } else {
            $this->where[] = [$column, $operator, $value];
        }

        return $this;
    }


    /**
     * Value must be in list
     *
     * @param  string $column   Column to search
     * @param  array  $values
     * @return $this
     */
    public function in($column, array $values)
    {
        return $this->where($column, 'in', $values);
    }


    /**
     * Value must not be in list
     *
     * @param  string $column   Column to search
     * @param  array  $values
     * @return $this
     */
    public function notIn($column, array $values)
    {
        return $this->where($column, '!in', $values);
    }


    /**
     * Value must exist in sub array
     *
     * @param  string $column   Column to search
     * @param  mixed  $value
     * @return $this
     */
    public function arrayHas($column, $value)
    {
        $this->where[] = [$column, 'array_has', $value];
        return $this;
    }


    /**
     * Value must not exist in sub array
     *
     * @param  string $column   Column to search
     * @param  mixed  $value
     * @return $this
     */
    public function arrayHasNot($column, $value)
    {
        $this->where[] = [$column, '!array_has', $value];
        return $this;
    }


    /**
     * Value must not be null
     *
     * @param  string $column
     * @return $this
     */
    public function isNull($column)
    {
        $this->where[] = [$column, '===', null];
        return $this;
    }


    /**
     * Value must be null
     *
     * @param  string $column
     * @return $this
     */
    public function notNull($column)
    {
        $this->where[] = [$column, '!==', null];
        return $this;
    }


    /**
     * Column must exist
     *
     * @param  string $column
     * @return $this
     */
    public function hasColumn($column)
    {
        $this->where[] = [$column, 'has_col', null];
        return $this;
    }


    /**
     * Column must not exist
     *
     * @param  string $column
     * @return $this
     */
    public function hasNotColumn($column)
    {
        $this->where[] = [$column, '!has_col', null];
        return $this;
    }


    /**
     * Order the table
     *
     * @param  array $data
     * @return array
     */
    protected function getData()
    {
        if (is_null($this->order[0])) {
            return $this->table->data['data'];
        }

        $items  = $this->table->data['data'];
        $col    = $this->order[0];
        $order  = $this->order[1];

        usort($items, function ($a, $b) use ($col, $order) {
            if (!isset($a[$col]) && !isset($b[$col])) {
                return 0;
            }

            if (is_array($a[$col]) || is_array($b[$col])) {
                return 0;
            }

            if (!isset($a[$col]) || is_array($a[$col])) {
                return $order == 'asc' ? -1 : 1;
            }

            if (!isset($b[$col]) || is_array($b[$col])) {
                return $order == 'asc' ? 1 : -1;
            }

            return $order == 'asc'
                ? strnatcmp((string) $a[$col], (string) $b[$col])
                : strnatcmp((string) $b[$col], (string) $a[$col]);
        });

        return $items;
    }


    /**
     * Convert a record set
     *
     * @param  array $rs
     * @return mixed
     */
    protected function convertItem($rs)
    {
        if ('array' == $this->returnAs) {
            return $rs;
        }

        if ('stdclass' == $this->returnAs) {
            return (object) $rs;
        }

        return new $this->returnAs($rs);
    }


    /**
     * Match an item with the where conditions
     *
     * @return boolean
     */
    protected function matchWhere($rs)
    {
        foreach ($this->where as $where) {
            list($key, $op, $test) = $where;
            $found = array_key_exists($key, $rs);
            $real  = $found ? $rs[$key] : null;

            if ($this->filters->match($op, $found, $real, $test) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a new random ID
     *
     * @return string
     */
    protected function generateId()
    {
        return '_' . bin2hex(openssl_random_pseudo_bytes(8));
    }
}
