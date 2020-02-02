<?php

namespace If_4 {
  function main() {
    if (true) {
      $b = "hello";
    } else {
      $b = "world";
    }
    $x = $b;
  }
}

namespace {
  \If_4\main(null);
}
