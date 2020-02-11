<?php

namespace Match_1 {
  function main() {
    $a = 4;
    if ($a == 0) {
      print("zero\n");
    } else if ($a == 1) {
      print("one\n");
    } else if ($a == 2) {
      print("two\n");
    } else {
      print("several\n");
    }
    return null;
  }
}

namespace {
  \Match_1\main(null);
}
