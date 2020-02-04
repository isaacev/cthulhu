<?php

namespace Match_8 {
  function test($things) {
    if (\count($things) == 0) {
      $c = "none";
    } else if (\count($things) == 1 && $things[0] == "a") {
      $c = "is the letter a";
    } else if (\count($things) == 1) {
      $_a = $things[0];
      $c = $_a;
    } else if (\count($things) >= 1) {
      $a_1 = $things[0];
      $rest = \array_slice($things, 1);
      $c = $a_1 . (string)\count($rest);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($c . "\n");
  }
  function main() {
    \Match_8\test([]);
    \Match_8\test([ "a" ]);
    \Match_8\test([
      "b",
      "c"
    ]);
    \Match_8\test([
      "d",
      "e",
      "f"
    ]);
  }
}

namespace {
  \Match_8\main(null);
}
