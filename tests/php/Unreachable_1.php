<?php

namespace Unreachable_1 {
  function main() {
    die("unreachable code was reached\n");
    return null;
  }
}

namespace {
  \Unreachable_1\main(null);
}
