<?php namespace Maer\FileDB;

class Filters
{
    protected $operators = [
        '='          => 'testEqual',
        '==='        => 'testEqualStrict',
        '!=='        => 'testNotEqualStrict',
        '!='         => 'testNotEqual',
        '<>'         => 'testNotEqual',
        '<'          => 'testLower',
        '>'          => 'testHigher',
        '<='         => 'testLowerOrEqual',
        '>='         => 'testHigherOrEqual',
        '*'          => 'testContains',
        '=*'         => 'testStartsWith',
        '*='         => 'testEndsWith',
        'in'         => 'testInList',
        '!in'        => 'testNotInList',
        'regex'      => 'testRegex',
        'func'       => 'testCallback',
        'array_has'  => 'testArrayHas',
        '!array_has' => 'testArrayHasNot',
        'has_col'    => 'testHasColumn',
        '!has_col'   => 'testHasNotColumn',
    ];

    public function match($op, $found, $real, $test)
    {
        if (!array_key_exists($op, $this->operators)) {
            return false;
        }

        if (!method_exists($this, $this->operators[$op])) {
            return false;
        }

        return call_user_func_array(
            [$this, $this->operators[$op]],
            [$found, $real, $test]
        );
    }

    public function testEqual($found, $real, $test)
    {
        return $found && $real == $test;
    }

    public function testEqualStrict($found, $real, $test)
    {
        return $found && $real === $test;
    }

    public function testNotEqual($found, $real, $test)
    {
        return !$found || $real != $test;
    }

    public function testNotEqualStrict($found, $real, $test)
    {
        return !$found || $real !== $test;
    }

    public function testLower($found, $real, $test)
    {
        return $found && $real < $test;
    }

    public function testHigher($found, $real, $test)
    {
        return $found && $real > $test;
    }

    public function testLowerOrEqual($found, $real, $test)
    {
        return $found && $real <= $test;
    }

    public function testHigherOrEqual($found, $real, $test)
    {
        return $found && $real >= $test;
    }

    public function testContains($found, $real, $test)
    {
        return $found && strpos($real, $test) !== false;
    }

    public function testStartsWith($found, $real, $test)
    {
        return $found && strpos($real, $test) === 0;
    }

    public function testEndsWith($found, $real, $test)
    {
        return $found && substr($real, strlen($test)) === $test;
    }

    public function testInList($found, $real, $test)
    {
        return $found && is_array($test) && in_array($real, $test);
    }

    public function testNotInList($found, $real, $test)
    {
        return $found && is_array($test) && !in_array($real, $test);
    }

    public function testCallback($found, $real, $test)
    {
        return call_user_func_array($test, [$real]);
    }

    public function testRegex($found, $real, $test)
    {
        return (bool) preg_match($test, $real);
    }

    public function testArrayHas($found, $real, $test)
    {
        return $found && is_array($real) && in_array($test, $real);
    }

    public function testArrayHasNot($found, $real, $test)
    {
        return !$found || !is_array($real) || !in_array($test, $real);
    }

    public function testHasColumn($found)
    {
        return $found;
    }

    public function testHasNotColumn($found)
    {
        return !$found;
    }
}
