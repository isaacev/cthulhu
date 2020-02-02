<?php

namespace If_5 {
  function main() {
    if (true) {
      "hello";
      $b = null;
    } else {
      $b = null;
    }
    $a = $b;
  }
}

namespace {
  \If_5\main(null);
}
