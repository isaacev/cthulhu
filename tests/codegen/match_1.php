<?php

namespace Kernel\Builtins {
  // #[inline]
  // #[construct]
  function _print($a) {
    print($a);
  }
}

namespace Io {
  // #[inline]
  function println($str) {
    \Kernel\Builtins\_print($str . "\n");
  }
}

namespace match_1 {
  // #[entry]
  function main() {
    $b = 2 + 2;
    if ($b == 0) {
      \Io\println("zero");
    } else if ($b == 1) {
      \Io\println("one");
    } else if ($b == 2) {
      \Io\println("two");
    } else if (true) {
      \Io\println("several");
    }
    $a;
  }
}

namespace {
  \match_1\main();
}
