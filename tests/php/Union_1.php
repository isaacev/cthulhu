<?php

namespace Union_1 {
  abstract class Foo {}
  class Bar extends \Union_1\Foo {
    function __construct() {
      // empty
    }
  }
  function main() {
    new \Union_1\Bar();
    return null;
  }
}

namespace {
  \Union_1\main(null);
}
