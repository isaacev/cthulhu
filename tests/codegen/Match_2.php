<?php

namespace Match_2 {
  function main() {
    $a = "abc";
    if ($a == "") {
      // empty
    } else if ($a == "abc") {
      // empty
    } else if (true) {
      -1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \Match_2\main(null);
}
