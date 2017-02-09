<?php namespace Maer\FileDB;

class QueryBuilder
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $where;

    /**
     * @var Filters
     */
    protected $filters;


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
        $data = ['id' => $this->generateId()] + $data;
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
     * Get records
     *
     * @return array
     */
    public function get()
    {
        if (!$this->where) {
            return array_values($this->table->data['data']);
        }

        $list = [];
        foreach ($this->table->data['data'] as $rs) {
            if ($this->matchWhere($rs)) {
                $list[] = $rs;
            }
        }

        return $list;
    }

    /**
     * Get the number of results the current query returns
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
        if (!$this->where) {
            return $this->data['data'] ? reset($this->data['data']) : null;
        }

        foreach ($this->table->data['data'] as $rs) {
            if ($this->matchWhere($rs)) {
                return $rs;
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
        $this->where = [];
        $this->where($column, $id);

        return $this->first();
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
