<?php

namespace If_3 {
  function main() {
    if (true) {
      $b = "hello";
    } else {
      $b = "world";
    }
    $b;
  }
}

namespace {
  \If_3\main(null);
}
