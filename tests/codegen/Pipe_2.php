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
    $b = $mm;
    if ($b instanceof \Prelude\Some) {
      $n = $b->{0};
      $c = new \Prelude\Some($f($n));
    } else if ($b instanceof \Prelude\None) {
      $c = new \Prelude\None();
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $a = $c;
    return $a;
  }
  function or_else($fallback, $x) {
    $b = $x;
    if ($b instanceof \Prelude\Some) {
      $_a = $b->{0};
      $c = $_a;
    } else if ($b instanceof \Prelude\None) {
      $c = $fallback;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $a = $c;
    return $a;
  }
  function main() {
    print((fn ($b) => \Pipe_2\or_else("nothing", $b))((fn ($b) => \Pipe_2\map('\Fmt\_int', $b))(new \Prelude\Some(123))) . "\n");
  }
}

namespace {
  \Pipe_2\main(null);
}
