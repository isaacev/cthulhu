<?php

namespace Kernel\Builtins {
  // #[inline]
  // #[construct]
  function _print($a) {
    print($a);
  }

  // #[inline]
  // #[construct]
  function cast_int_to_string($a) {
    return (string)$a;
  }
}

namespace Io {
  // #[inline]
  function println($str) {
    \Kernel\Builtins\_print($str . "\n");
  }
}

namespace Fmt {
  // #[inline]
  function int($i) {
    return \Kernel\Builtins\cast_int_to_string($i);
  }
}

namespace curry_1 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  // #[entry]
  function main() {
    $x = '\curry_1\foo';
    \Io\println(\Fmt\int($x(1, 2, 3)));
  }
}

namespace {
  \curry_1\main();
}
