<?php

namespace union_1 {
  abstract class Foo {}

  class Bar extends \union_1\Foo {}

  // #[entry]
  function main() {
    new \union_1\Bar();
  }
}

namespace {
  \union_1\main();
}