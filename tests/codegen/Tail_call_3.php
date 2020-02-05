<?php

namespace Tail_call_3 {
  function sum($d, $e) {
    while (true) {
      $acc = $d;
      $rest = $e;
      if (\count($rest) == 0) {
        return $acc;
      } else if (\count($rest) >= 1) {
        $x = $rest[0];
        $xs = \array_slice($rest, 1);
        $d = $acc + $x;
        $e = $xs;
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
  }
}

namespace {
  \Tail_call_3\main(null);
}
