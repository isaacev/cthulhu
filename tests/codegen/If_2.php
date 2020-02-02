<?php

namespace If_2 {
  function main() {
    if (true) {
      $b = "hello";
    } else {
      $b = "world";
    }
    return $b;
  }
}

namespace {
  \If_2\main(null);
}
