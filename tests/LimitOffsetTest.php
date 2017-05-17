<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Maer\FileDB\Filters
 */
class LimitOffset extends TestCase
{
    /**
    * @covers ::limit
    */
    public function testLimit()
    {
        $this->reset();

        $items = db('test')
            ->limit(10)
            ->get();

        $this->assertEquals(10, count($items));
    }

    /**
    * @covers ::limit->orderBy
    */
    public function testLimitOrderByAsc()
    {
        $this->reset();

        $items = db('test')
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        $this->assertEquals('Item #1', $items[0]['name']);
        $this->assertEquals('Item #10', $items[9]['name']);
    }

    /**
    * @covers ::limit->orderBy
    */
    public function testLimitOrderByDesc()
    {
        $this->reset();

        $items = db('test')
            ->orderBy('name', 'desc')
            ->limit(10)
            ->get();

        $this->assertEquals('Item #100', $items[0]['name']);
        $this->assertEquals('Item #91', $items[9]['name']);
    }


    /**
    * @covers ::offset
    */
    public function testOffset()
    {
        $this->reset();

        $items = db('test')
            ->orderBy('name', 'asc')
            ->offset(50)
            ->get();

        $this->assertEquals(50, count($items));
        $this->assertEquals('Item #51', $items[0]['name']);
    }


    /**
    * @covers ::limit->offset
    */
    public function testLimitOffsetOrderByAsc()
    {
        $this->reset();

        $items = db('test')
            ->orderBy('name', 'asc')
            ->limit(10)
            ->offset(50)
            ->get();

        $this->assertEquals(10, count($items));
        $this->assertEquals('Item #51', $items[0]['name']);
        $this->assertEquals('Item #60', $items[9]['name']);
    }

    /**
    * @covers ::limit->offset
    */
    public function testLimitOffsetOrderByDesc()
    {
        $this->reset();

        $items = db('test')
            ->orderBy('name', 'desc')
            ->limit(10)
            ->offset(50)
            ->get();

        $this->assertEquals(10, count($items));
        $this->assertEquals('Item #50', $items[0]['name']);
        $this->assertEquals('Item #41', $items[9]['name']);
    }

    /**
     * Reset the database
     * @return array $ids
     */
    protected function reset()
    {
        db('test')->truncate();

        $data = [];
        for ($i = 1; $i <= 100; $i++) {
            $data[] = ['name' => 'Item #' . $i];
        }

        return db('test')->batchInsert($data);

    }


}
