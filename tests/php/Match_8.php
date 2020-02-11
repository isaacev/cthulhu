<?php

namespace Match_8 {
  function test($things) {
    if (\count($things) == 0) {
      $x = "none";
    } else if (\count($things) == 1 && $things[0] == "a") {
      $x = "is the letter a";
    } else if (\count($things) == 1) {
      $_a = $things[0];
      $x = $_a;
    } else if (\count($things) >= 1) {
      $a_1 = $things[0];
      $rest = \array_slice($things, 1);
      $x = $a_1 . (string)\count($rest);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($x . "\n");
    return null;
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
    return null;
  }
}

namespace {
  \Match_8\main(null);
}
