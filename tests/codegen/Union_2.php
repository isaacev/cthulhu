<?php

namespace Union_2 {
  abstract class Foo {}
  class Bar extends \Union_2\Foo {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  function main() {
    new \Union_2\Bar(123);
  }
}

namespace {
  \Union_2\main(null);
}
