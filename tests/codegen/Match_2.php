<?php

namespace Match_2 {
  function main() {
    $b = "abc";
    if ($b == "") {
      $c = 0;
    } else if ($b == "abc") {
      $c = 3;
    } else if (true) {
      $c = -1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $y = $c + 1;
  }
}

namespace {
  \Match_2\main(null);
}
