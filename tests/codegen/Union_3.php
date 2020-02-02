<?php

namespace union_3 {
  abstract class Foo {}

  class Bar extends \union_3\Foo {
    public $integer;
    function __construct($args) {
      $this->integer = $args["integer"];
    }
  }

  // #[entry]
  function main() {
    new \union_3\Bar([
      "integer" => 123
    ]);
  }
}

namespace {
  \union_3\main();
}
