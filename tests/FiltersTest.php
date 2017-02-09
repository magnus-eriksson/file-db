<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Maer\FileDB\Filters
 */
class FiltersTest extends TestCase
{
    protected $filter;

    public function __construct()
    {
        $this->filter = new Maer\FileDB\Filters;
    }

    /**
    * @covers ::testEqual
    */
    public function testEqual()
    {
        $this->assertTrue(
            $this->filter->match('=', true, 'foo', 'foo')
        );

        $this->assertFalse(
            $this->filter->match('=', true, 'foo', 'bar')
        );
    }

    /**
    * @covers ::testEqualStrict
    */
    public function testEqualStrict()
    {
        $this->assertTrue(
            $this->filter->match('===', true, '1234', '1234')
        );

        $this->assertFalse(
            $this->filter->match('===', true, '1234', 1234)
        );
    }

    /**
    * @covers ::testNotEqual
    */
    public function testNotEqual()
    {
        $this->assertTrue(
            $this->filter->match('!=', true, 'foo', 'bar')
        );

        $this->assertFalse(
            $this->filter->match('!=', true, 'foo', 'foo')
        );
    }

    /**
    * @covers ::testNotEqualStrict
    */
    public function testNotEqualStrict()
    {
        $this->assertTrue(
            $this->filter->match('!==', true, '123', 123)
        );

        $this->assertFalse(
            $this->filter->match('!==', true, 123, 123)
        );
    }

    /**
    * @covers ::testLower
    */
    public function testLower()
    {
        $this->assertTrue(
            $this->filter->match('<', true, 10, 20)
        );

        $this->assertFalse(
            $this->filter->match('<', true, 20, 20)
        );
    }

    /**
    * @covers ::testHigher
    */
    public function testHigher()
    {
        $this->assertTrue(
            $this->filter->match('>', true, 20, 10)
        );

        $this->assertFalse(
            $this->filter->match('>', true, 20, 20)
        );
    }

    /**
    * @covers ::testLowerOrEqual
    */
    public function testLowerOrEqual()
    {
        $this->assertTrue(
            $this->filter->match('<=', true, 20, 20)
        );

        $this->assertFalse(
            $this->filter->match('<=', true, 20, 10)
        );
    }

    /**
    * @covers ::testHigherOrEqual
    */
    public function testHigherOrEqual()
    {
        $this->assertTrue(
            $this->filter->match('>=', true, 20, 20)
        );

        $this->assertFalse(
            $this->filter->match('>=', true, 10, 20)
        );
    }

    /**
    * @covers ::testContains
    */
    public function testContains()
    {
        $this->assertTrue(
            $this->filter->match('*', true, 'foobar', 'oba')
        );

        $this->assertFalse(
            $this->filter->match('*', true, 'foobar', 'abc')
        );
    }

    /**
    * @covers ::testStartsWith
    */
    public function testStartsWith()
    {
        $this->assertTrue(
            $this->filter->match('=*', true, 'foobar', 'foo')
        );

        $this->assertFalse(
            $this->filter->match('=*', true, 'foobar', 'bar')
        );
    }

    /**
    * @covers ::testEndsWith
    */
    public function testEndsWith()
    {
        $this->assertTrue(
            $this->filter->match('*=', true, 'foobar', 'bar')
        );

        $this->assertFalse(
            $this->filter->match('*=', true, 'foobar', 'foo')
        );
    }

    /**
    * @covers ::testInList
    */
    public function testInList()
    {
        $this->assertTrue(
            $this->filter->match('in', true, 'foo', ['bar', 'foo'])
        );

        $this->assertFalse(
            $this->filter->match('in', true, 'foo', ['hello', 'world'])
        );
    }

    /**
    * @covers ::testNotInList
    */
    public function testNotInList()
    {
        $this->assertTrue(
            $this->filter->match('!in', true, 'foo', ['hello', 'world'])
        );

        $this->assertFalse(
            $this->filter->match('!in', true, 'foo', ['foo', 'bar'])
        );
    }

    /**
    * @covers ::testCallback
    */
    public function testCallback()
    {
        $this->assertTrue(
            $this->filter->match('func', true, 'foo', function ($value) {
                return 'foo' == $value;
            })
        );

        $this->assertFalse(
            $this->filter->match('func', true, 'foo', function ($value) {
                return 'bar' == $value;
            })
        );
    }

    /**
    * @covers ::testRegex
    */
    public function testRegex()
    {
        $this->assertTrue(
            $this->filter->match('regex', true, 'hello123', '/^([\w]+)$/')
        );

        $this->assertFalse(
            $this->filter->match('regex', true, 'hello 1234', '/^([\w]+)$/')
        );
    }

    /**
    * @covers ::testArrayHas
    */
    public function testArrayHas()
    {
        $this->assertTrue(
            $this->filter->match('array_has', true, ['hello', 'world'], 'hello')
        );

        $this->assertFalse(
            $this->filter->match('array_has', true, ['hello', 'world'], 'foo')
        );

        $this->assertFalse(
            $this->filter->match('array_has', true, 'hello', 'foo')
        );
    }

    /**
    * @covers ::testArrayHas
    */
    public function testArrayHasNot()
    {
        $this->assertTrue(
            $this->filter->match('!array_has', true, ['hello', 'world'], 'foo')
        );

        $this->assertTrue(
            $this->filter->match('!array_has', true, 'nada', 'foo')
        );

        $this->assertFalse(
            $this->filter->match('!array_has', true, ['hello', 'world'], 'hello')
        );
    }

    /**
    * @covers ::testHasColumn
    */
    public function testHasColumn()
    {
        $this->assertTrue(
            $this->filter->match('has_col', true, null, null)
        );

        $this->assertFalse(
            $this->filter->match('has_col', false, null, null)
        );
    }

    /**
    * @covers ::testHasNotColumn
    */
    public function testHasNotColumn()
    {
        $this->assertTrue(
            $this->filter->match('!has_col', false, null, null)
        );

        $this->assertFalse(
            $this->filter->match('!has_col', true, null, null)
        );
    }
}
