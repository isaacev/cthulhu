<?php

namespace Match_9 {
  function test($things) {
    if (\count($things) == 0) {
      $x = "none";
    } else if (\count($things) == 1) {
      $x = "one";
    } else if (\count($things) >= 0) {
      $x = "multiple";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($x . "\n");
    return null;
  }

  function main() {
    \Match_9\test([]);
    \Match_9\test([ 1 ]);
    \Match_9\test([
      1,
      2
    ]);
    \Match_9\test([
      1,
      3,
      4
    ]);
    return null;
  }
}

namespace {
  \Match_9\main(null);
}
