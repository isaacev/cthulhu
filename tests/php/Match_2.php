<?php

namespace Match_2 {
  function main() {
    $a = "abc";
    if ($a == "") {
      // empty
    } else if ($a == "abc") {
      // empty
    } else {
      -1;
    }
    return null;
  }
}

namespace {
  \Match_2\main(null);
}
