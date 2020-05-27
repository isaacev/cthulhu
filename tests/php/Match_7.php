<?php

namespace Match_7 {
  function test($things) {
    if (\count($things) == 0) {
      $x = "none";
    } else if (\count($things) == 1) {
      $x = "one";
    } else if (\count($things) >= 0) {
      $rest = \array_slice($things, 0);
      $x = (string)\count($rest);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($x . "\n");
    return null;
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
    return null;
  }
}

namespace {
  \Match_7\main(null);
}
