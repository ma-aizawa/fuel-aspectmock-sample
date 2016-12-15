<?php
namespace Model\Hoge;

use AspectMock\Test as test;

class HogeTest extends \PHPUnit\Framework\TestCase
{
  public function testHoge()
  {
    $model = new \Model\Hoge\Hoge();
    $this->assertEquals('hoge', $model->hoge());
  }

  public function testStaticHoge()
  {
    test::double('Model\Hoge\Hoge', ['staticHoge' => 'fuga']);
    $this->assertEquals('fuga', \Model\Hoge\Hoge::staticHoge());
  }

  public function testGetHogeList()
  {
    $model = new \Model\Hoge\Hoge();
    $this->assertEquals(['first' => 'hoge', 'second' => 'fuga'], $model->getHogeList());
  }

  protected function tearDown()
  {
    test::clean();
  }
}
