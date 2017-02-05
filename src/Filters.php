<?php namespace Maer\FileDB;

class Filters
{
    protected $operators = [
        '='     => 'equal',
        '==='   => 'equal_strict',
        '!='    => 'not_equal',
        '<>'    => 'not_equal',
        '<'     => 'lower',
        '>'     => 'higher',
        '<='    => 'lower_or_equal',
        '>='    => 'higher_or_equal',
        '*'     => 'contains',
        '=*'    => 'starts_with',
        '*='    => 'ends_with',
        'in'    => 'in_list',
        '!in'   => 'not_in_list',
        'regex' => 'regex',
        'func'  => 'callback',
    ];

    public function match($op, $found, $real, $test)
    {
        if (!array_key_exists($op, $this->operators)) {
            return false;
        }

        $op = str_replace('_', ' ', $this->operators[$op]);
        $op = ucwords($op);
        $op = str_replace(' ', '', $op);

        return call_user_func_array([$this, "test{$op}"], [$found, $real, $test]);
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
}
