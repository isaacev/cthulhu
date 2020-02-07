<?php

namespace Tail_call_1 {
  function to_zero($n) {
    if ($n <= 0) {
      print("all done" . "\n");
    } else {
      print((string)$n . "\n");
      \Tail_call_1\to_zero($n - 1);
    }
    return null;
  }
  function main() {
    \Tail_call_1\to_zero(100);
    return null;
  }
}

namespace {
  \Tail_call_1\main(null);
}
