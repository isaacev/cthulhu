<?php

namespace union_2 {
  abstract class Foo {}

  class Bar extends \union_2\Foo {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  // #[entry]
  function main() {
    new \union_2\Bar(123);
  }
}

namespace {
  \union_2\main();
}