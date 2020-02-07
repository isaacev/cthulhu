<?php

namespace Tail_call_2 {
  function to_zero($a) {
    while (true) {
      $n = $a;
      if ($n <= 0) {
        print("all done" . "\n");
        return;
      } else {
        print((string)$n . "\n");
        $a = $n - 1;
        continue;
      }
    }
  }
  function main() {
    \Tail_call_2\to_zero(-10);
  }
}

namespace {
  \Tail_call_2\main(null);
}
