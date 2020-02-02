<?php

namespace If_2 {
  function main() {
    if (true) {
      $b = "hello";
    } else {
      $b = "world";
    }
    $a = $b;
    return $a;
  }
}

namespace {
  \If_2\main(null);
}
