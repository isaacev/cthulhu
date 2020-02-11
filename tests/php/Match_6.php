<?php

namespace Match_6 {
  function test($things) {
    if (\count($things) == 0) {
      $x = "none";
    } else if (\count($things) == 1) {
      $x = "one";
    } else if (\count($things) >= 0) {
      \array_slice($things, 0);
      $x = "multiple";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($x . "\n");
    return null;
  }
  function main() {
    \Match_6\test([]);
    \Match_6\test([ 1 ]);
    \Match_6\test([
      1,
      2
    ]);
    \Match_6\test([
      1,
      3,
      4
    ]);
    return null;
  }
}

namespace {
  \Match_6\main(null);
}
