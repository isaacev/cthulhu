<?php

namespace Str_5 {
  function main() {
    $hello = "world";
    print("\$\$hello" . "\n");
  }
}

namespace {
  \Str_5\main(null);
}
