<?php

namespace Prelude\Maybe {
  function map($f, $m) {
    if ($m instanceof \Prelude\Some) {
      $_a = $m->{0};
      return new \Prelude\Some($f($_a));
    } else if ($m instanceof \Prelude\None) {
      return new \Prelude\None();
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }

  function or_ok($err, $m) {
    if ($m instanceof \Prelude\Some) {
      $_a = $m->{0};
      return new \Prelude\Ok($_a);
    } else if ($m instanceof \Prelude\None) {
      return new \Prelude\Err($err);
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace Prelude\Result {
  function flatten($r) {
    if ($r instanceof \Prelude\Ok) {
      $_a = $r->{0};
      return $_a;
    } else if ($r instanceof \Prelude\Err) {
      $a_1 = $r->{0};
      return $a_1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

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
  abstract class Result {}
  class Ok extends \Prelude\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Err extends \Prelude\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
}

namespace Chain_1 {
  function main() {
    '\Prelude\Result\flatten';
    $a = \Prelude\Maybe\or_ok("<ERROR>", \Prelude\Maybe\map(function($_a) {
      return "(" . $_a . ")";
    }, new \Prelude\Some("hello world")));
    if ($a instanceof \Prelude\Ok) {
      $_a = $a->{0};
      $b = $_a;
    } else if ($a instanceof \Prelude\Err) {
      $a_1 = $a->{0};
      $b = $a_1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    return print($b . "\n");
  }
}

namespace {
  \Chain_1\main(null);
}
