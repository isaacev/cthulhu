<?php

namespace Match_7 {
  function test($things) {
    if (\count($things) == 0) {
      $c = "none";
    } else if (\count($things) == 1) {
      $c = "one";
    } else if (\count($things) >= 0) {
      $rest = \array_slice($things, 0);
      $c = (string)\count($rest);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($c . "\n");
  }
  function main() {
    \Match_7\test([]);
    \Match_7\test([ 1 ]);
    \Match_7\test([
      1,
      2
    ]);
    \Match_7\test([
      1,
      3,
      4
    ]);
  }
}

namespace {
  \Match_7\main(null);
}
