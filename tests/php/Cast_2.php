<?php

namespace Cast_2 {
  function main() {
    $b = true;
    if ($b == true) {
      $a = "true";
    } else if ($b == false) {
      $a = "false";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($a . "\n");
    return null;
  }
}

namespace {
  \Cast_2\main(null);
}
