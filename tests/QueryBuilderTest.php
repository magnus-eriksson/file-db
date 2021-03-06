<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Maer\FileDB\Filters
 */
class QueryBuilderTest extends TestCase
{
    /**
    * @covers ::meta
    */
    public function testMeta()
    {
        $this->assertInternalType(
            'integer',
            db('test')->meta('created')
        );
    }

    /**
    * @covers ::insert
    */
    public function testInsert()
    {
        $this->reset();

        $this->assertEquals(3, db('test')->count());
    }

    /**
    * @covers ::where
    */
    public function testWhere()
    {
        $this->reset();

        $result = db('test')
            ->where('name', 'foo')
            ->count();

        $this->assertEquals(1, $result);

        $result = db('test')
            ->where('name', '!=', 'foo')
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::update
    */
    public function testUpdate()
    {
        $this->reset();

        db('test')
            ->where('name', 'foo')
            ->update(['name' => 'bar']);

        $result = db('test')
            ->where('name', 'bar')
            ->get();

        $this->assertEquals(1, count($result));
    }

    /**
    * @covers ::replace
    */
    public function testReplace()
    {
        $this->reset();

        db('test')
            ->where('name', 'foo')
            ->replace(['name' => 'bar']);

        $result = db('test')
            ->where('name', 'bar')
            ->first();

        $this->assertEquals(2, count($result));
    }

    /**
    * @covers ::delete
    */
    public function testDelete()
    {
        $this->reset();

        db('test')
            ->where('name', 'foo')
            ->delete();

        $result = db('test')
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::find
    */
    public function testFind()
    {
        $ids = $this->reset();

        $result = db('test')
            ->find($ids[1]);

        $this->assertEquals('foo2', $result['name'] ?? null);

        $result = db('test')
            ->find('foo3', 'name');

        $this->assertEquals('foo3', $result['name']);
    }

    /**
    * @covers ::in
    */
    public function testIn()
    {
        $ids = $this->reset();

        $result = db('test')
            ->in('name', ['foo2', 'foo3'])
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::notIn
    */
    public function testNotIn()
    {
        $ids = $this->reset();

        $result = db('test')
            ->notIn('name', ['foo2', 'foo3'])
            ->count();

        $this->assertEquals(1, $result);
    }

    /**
    * @covers ::arrayHas
    */
    public function testArrayHas()
    {
        $ids = $this->reset();

        $result = db('test')
            ->arrayHas('list', 'world')
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::arrayHasNot
    */
    public function testArrayHasNot()
    {
        $ids = $this->reset();

        $result = db('test')
            ->arrayHasNot('list', 'world')
            ->count();

        $this->assertEquals(1, $result);
    }

    /**
    * @covers ::isNull
    */
    public function testIsNull()
    {
        $ids = $this->reset();

        $result = db('test')
            ->isNull('empty')
            ->count();

        $this->assertEquals(1, $result);
    }

    /**
    * @covers ::notNull
    */
    public function testNotNull()
    {
        $ids = $this->reset();

        $result = db('test')
            ->notNull('empty')
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::has
    */
    public function testHas()
    {
        $ids = $this->reset();

        $result = db('test')
            ->hasColumn('number')
            ->count();

        $this->assertEquals(2, $result);
    }

    /**
    * @covers ::hasNot
    */
    public function testHasNot()
    {
        $ids = $this->reset();

        $result = db('test')
            ->hasNotColumn('empty')
            ->count();

        $this->assertEquals(1, $result);
    }

    /**
    * @covers ::asObj
    */
    public function testAsObj()
    {
        $ids = $this->reset();

        $result = db('test')
            ->asObj()
            ->find('foo', 'name');

        $this->assertInternalType('object', $result);
        $this->assertEquals('foo', $result->name);

        $result = db('test')
            ->asObj('DataObject')
            ->find('foo', 'name');

        $this->assertInternalType('object', $result);
        $this->assertEquals('foo', $result->name);
    }


    /**
    * @covers ::orderBy
    */
    public function testOrderByAsc()
    {
        db('test')->truncate();
        db('test')->batchInsert([
            [
                'name'  => 'third',
                'sort'  => 3,
                'sort2' => ['test'],
            ],
            [
                'name'  => 'first',
                'sort'  => 1,
                'sort2' => ['test'],
            ],
            [
                'name'  => 'fourth',
                'sort'  => 4,
                'sort2' => 999,
            ],
            [
                'name'  => 'second',
                'sort'  => 2,
                'sort2' => ['test'],
            ],
        ]);

        $result = db('test')
            ->orderBy('sort', 'asc')
            ->get();

        $this->assertEquals('first', $result[0]['name']);
        $this->assertEquals('fourth', $result[3]['name']);

        $result = db('test')
            ->orderBy('name')
            ->get();

        $this->assertEquals('first', $result[0]['name']);
        $this->assertEquals('third', $result[3]['name']);

        $result = db('test')
            ->orderBy('sort2')
            ->get();

        $this->assertEquals('third', $result[0]['name']);
        $this->assertEquals(4, count($result));
    }

    /**
    * @covers ::orderBy
    */
    public function testOrderByDesc()
    {
        db('test')->truncate();
        db('test')->batchInsert([
            [
                'name'  => 'third',
                'sort'  => 3,
                'sort2' => ['test'],
            ],
            [
                'name'  => 'first',
                'sort'  => 1,
                'sort2' => ['test'],
            ],
            [
                'name'  => 'fourth',
                'sort'  => 4,
                'sort2' => 999,
            ],
            [
                'name'  => 'second',
                'sort'  => 2,
                'sort2' => ['test'],
            ],
        ]);

        $result = db('test')
            ->orderBy('sort', 'desc')
            ->get();

        $this->assertEquals('first', $result[3]['name']);
        $this->assertEquals('fourth', $result[0]['name']);

        $result = db('test')
            ->orderBy('name', 'desc')
            ->get();

        $this->assertEquals('first', $result[3]['name']);
        $this->assertEquals('third', $result[0]['name']);

        $result = db('test')
            ->orderBy('sort2', 'desc')
            ->get();

        $this->assertEquals('third', $result[0]['name']);
        $this->assertEquals(4, count($result));
    }

    /**
     * Reset the database
     * @return array $ids
     */
    protected function reset()
    {
        db('test')->truncate();
        return db('test')->batchInsert([
            [
                'name'   => 'foo',
                'number' => 1234,
                'empty'  => null,
                'list'   => ['hello', 'world'],
            ],
            [
                'name'   => 'foo2',
                'number' => 1234,
                'empty'  => 'not null',
                'list'   => ['hello2', 'world'],
            ],
            [
                'name'   => 'foo3',
                'list'   => ['hello3', 'world3'],
            ],
        ]);

    }
}
