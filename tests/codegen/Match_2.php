<?php

namespace Match_2 {
  function main() {
    $b = "abc";
    if ($b == "") {
      // empty
    } else if ($b == "abc") {
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
