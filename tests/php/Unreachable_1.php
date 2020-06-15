<?php

namespace runtime {
  function unreachable($line, $file) {
    printf("unreachable on line %d in %s\n", $line, $file);
    exit(1);
  }
}

namespace Unreachable_1 {
  function main() {
    \runtime\unreachable(3, "TEST_DIR/php/Unreachable_1.cth");
    return null;
  }
}

namespace {
  \Unreachable_1\main(null);
}
