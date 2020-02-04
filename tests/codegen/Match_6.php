<?php

namespace Match_6 {
  function test($things) {
    if (\count($things) == 0) {
      $c = "none";
    } else if (\count($things) == 1) {
      $c = "one";
    } else if (\count($things) >= 0) {
      $rest = \array_slice($things, 0);
      $c = "multiple";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($c . "\n");
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
  }
}

namespace {
  \Match_6\main(null);
}
