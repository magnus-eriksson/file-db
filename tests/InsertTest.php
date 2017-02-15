<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Maer\FileDB\Filters
 */
class InsertTest extends TestCase
{
    /**
    * @covers ::meta
    */
    public function testInsertWithId()
    {
        db('test')->truncate();

        $id = db('test')->insert([
            'id'    => '123',
            'name'  => 'hello'
        ]);

        $result = db('test')->where('name', 'hello')->first();

        $this->assertEquals('123', $id);
        $this->assertEquals('123', $result['id']);
    }

    /**
    * @covers ::meta
    */
    public function testInsertWithIdDuplicate()
    {
        db('test')->truncate();

        db('test')->insert([
            'id'    => '123',
            'name'  => 'foo'
        ]);

        $id = db('test')->insert([
            'id'    => '123',
            'name'  => 'bar'
        ]);

        $result = db('test')->where('name', 'bar')->first();

        $this->assertEquals(null, $id);
        $this->assertEquals(null, $result);
    }

    /**
    * @covers ::meta
    */
    public function testBatchInsertWithId()
    {
        db('test')->truncate();

        $id = db('test')->batchInsert([
            [
                'id'    => '123',
                'name'  => 'hello'
            ],
            [
                'id'    => '124',
                'name'  => 'hello'
            ],
        ]);

        $result = db('test')->get();

        $this->assertEquals('123', $result[0]['id']);
        $this->assertEquals('124', $result[1]['id']);
    }

    /**
    * @covers ::meta
    */
    public function testBatchInsertWithIdDuplicate()
    {
        db('test')->truncate();

        db('test')->insert([
            'id'    => '123',
            'name'  => 'foo'
        ]);

        $id = db('test')->batchInsert([
            [
                'id'    => '123',
                'name'  => 'hello'
            ],
        ]);

        $result = db('test')->where('id', '123')->get();

        $this->assertEquals(0, count($id));
        $this->assertEquals(1, count($result));
        $this->assertEquals('foo', $result[0]['name']);
    }
}
