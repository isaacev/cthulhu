<?php

namespace Concat_2 {
  function main() {
    $s = "abcdef";
    print($s . "\n");
    return null;
  }
}

namespace {
  \Concat_2\main(null);
}
