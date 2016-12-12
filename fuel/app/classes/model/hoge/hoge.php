<?php
namespace Model\Hoge;

class Hoge extends \Fuel\Core\Model
{
  const HOGE_LIST = array(
    "first" => "hoge",
    "second" => "fuga"
  );

  public function hoge()
  {
    return "hoge";
  }

  public static function staticHoge()
  {
    return "hoge";
  }
}
