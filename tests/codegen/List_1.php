<?php

namespace Kernel\Types {
  abstract class Maybe {}

  class Just extends \Kernel\Types\Maybe {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class None extends \Kernel\Types\Maybe {}
}

namespace Kernel\Builtins {
  // #[inline]
  function array_key_exists($a, $b) {
    return \array_key_exists($a, $b);
  }
}

namespace _List {
  // #[inline]
  function nth($ls, $n) {
    if (\Kernel\Builtins\array_key_exists($n, $ls)) {
      $a = new \Kernel\Types\Just($ls[$n]);
    } else {
      $a = new \Kernel\Types\None();
    }
    return $a;
  }
}

namespace list_1 {
  function or_else($m, $f) {
    if ($m instanceof \Kernel\Types\Just) {
      $p = $m->{0};
      $a = $p;
    } else if ($m instanceof \Kernel\Types\None) {
      $a = $f;
    }
    return $a;
  }

  // #[entry]
  function main() {
    $l = [
      1,
      2,
      3
    ];
    $n = 4 + \list_1\or_else(\_List\nth($l, 0), -1);
  }
}

namespace {
  \list_1\main();
}
