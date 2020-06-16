<?php

namespace Concat_4 {
  function main() {
    $x = "def";
    $s = "abc" . $x;
    print($s . "\n");
    return null;
  }
}

namespace {
  \Concat_4\main(null);
}
