<?php

namespace Union_3 {
  abstract class Foo {}
  class Bar extends \Union_3\Foo {
    public $integer;
    function __construct($a) {
      $this->integer = $a["integer"];
    }
  }
  function main() {
    new \Union_3\Bar([
      "integer" => 123
    ]);
  }
}

namespace {
  \Union_3\main(null);
}
