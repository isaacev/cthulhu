<?php

namespace Name_1 {
  abstract class Thing {}
  class Blah extends \Name_1\Thing {
    public $foo;
    function __construct($a) {
      $this->foo = $a["foo"];
    }
  }
  function main() {
    $foo = "bar";
    $bar = new \Name_1\Blah([
      "foo" => "def"
    ]);
    print($foo . "\n");
    if ($bar instanceof \Name_1\Blah) {
      $x = $bar->foo;
      return print($x . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \Name_1\main(null);
}
