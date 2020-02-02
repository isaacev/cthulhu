<?php

namespace Prelude {
  abstract class Maybe {}
  class Some extends \Prelude\Maybe {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class None extends \Prelude\Maybe {
    function __construct() {
      // empty
    }
  }
}

namespace Fmt {
  function _int($i) {
    $a = (string)$i;
    return $a;
  }
}

namespace Pipe_2 {
  function map($f, $mm) {
    if ($mm instanceof \Prelude\Some) {
      $n = $mm->{0};
      $c = new \Prelude\Some($f($n));
    } else if ($mm instanceof \Prelude\None) {
      $c = new \Prelude\None();
    } else {
      die("match expression did not cover all possibilities\n");
    }
    return $c;
  }
  function or_else($fallback, $x) {
    if ($x instanceof \Prelude\Some) {
      $_a = $x->{0};
      $c = $_a;
    } else if ($x instanceof \Prelude\None) {
      $c = $fallback;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    return $c;
  }
  function main() {
    print((fn ($b) => \Pipe_2\or_else("nothing", $b))((fn ($b) => \Pipe_2\map('\Fmt\_int', $b))(new \Prelude\Some(123))) . "\n");
  }
}

namespace {
  \Pipe_2\main(null);
}
