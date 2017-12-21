<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Maer\FileDB\Filters
 */
class InsertUpdateTest extends TestCase
{
    public function __construct()
    {

    }

    /**
    * @covers ::meta
    */
    public function testInsertUpdateMultiple()
    {
        $this->reset();
        $result = db('test')->where('test', 'foo')->insertUpdate(['name' => 'foobar']);

        $this->assertSame(2, $result, 'Multiple insertUpdate');

        $rs = db('test')->where('name', 'foobar')->get();

        $this->assertSame('1', $rs[0]['id'], 'Multiple updates');
        $this->assertSame('2', $rs[1]['id'], 'Multiple updates');
    }

    /**
    * @covers ::meta
    */
    public function testInsertUpdateInsert()
    {
        $this->reset();
        $result = db('test')
            ->where('test', 'foobar')->insertUpdate([
                'id'   => '4',
                'name' => 'foobar',
                'test' => 'foobar'
            ]);

        $this->assertSame('4', $result, 'Insert (insert)');

        $rs = db('test')->where('name', 'foobar')->get();

        $this->assertSame('4', $rs[0]['id'], 'Insert values');
    }

    protected function reset()
    {
        db('test')->truncate();

        $id = db('test')->batchInsert([
            [
                'id'    => '1',
                'name'  => 'foo-1',
                'test'  => 'foo',
            ],
            [
                'id'    => '2',
                'name'  => 'foo-2',
                'test'  => 'foo',
            ],
            [
                'id'    => '3',
                'name'  => 'bar-1',
                'test'  => 'bar',
            ],
        ]);
    }
}
