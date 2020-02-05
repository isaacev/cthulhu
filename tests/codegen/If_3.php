<?php

namespace If_3 {
  function main() {
    if (true) {
      $b = "hello";
    } else {
      $b = "world";
    }
  }
}

namespace {
  \If_3\main(null);
}
