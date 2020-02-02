<?php

namespace If_2 {
  function main() {
    if (true) {
      return "hello";
    } else {
      return "world";
    }
  }
}

namespace {
  \If_2\main(null);
}
