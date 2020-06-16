<?php

namespace Concat_3 {
  function main() {
    $s = "abc123";
    print($s . "\n");
    return null;
  }
}

namespace {
  \Concat_3\main(null);
}
