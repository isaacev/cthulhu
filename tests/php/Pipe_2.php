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
    return (string)$i;
  }
}

namespace Pipe_2 {
  function map($f, $mm) {
    if ($mm instanceof \Prelude\Some) {
      $n = $mm->{0};
      return new \Prelude\Some($f($n));
    } else if ($mm instanceof \Prelude\None) {
      return new \Prelude\None();
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }

  function or_else($fallback, $x) {
    if ($x instanceof \Prelude\Some) {
      $_a = $x->{0};
      return $_a;
    } else if ($x instanceof \Prelude\None) {
      return $fallback;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }

  function main() {
    print(\Pipe_2\or_else("nothing", \Pipe_2\map('\Fmt\_int', new \Prelude\Some(123))) . "\n");
    return null;
  }
}

namespace {
  \Pipe_2\main(null);
}
