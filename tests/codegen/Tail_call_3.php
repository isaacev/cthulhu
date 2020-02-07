<?php

namespace Tail_call_3 {
  function sum($b, $c) {
    while (true) {
      $acc = $b;
      $rest = $c;
      if (\count($rest) == 0) {
        return $acc;
      } else if (\count($rest) >= 1) {
        $x = $rest[0];
        $xs = \array_slice($rest, 1);
        $b = $acc + $x;
        $c = $xs;
        continue;
      } else {
        die("match expression did not cover all possibilities\n");
      }
    }
  }
  function main() {
    print((string)\Tail_call_3\sum(0, [
      1,
      2,
      3,
      4,
      5,
      6
    ]) . "\n");
    return null;
  }
}

namespace {
  \Tail_call_3\main(null);
}
