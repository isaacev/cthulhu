<?php

namespace runtime {
  function curry($fn, $argv) {
    $arity = (new \ReflectionFunction($fn))->getNumberOfParameters();
    $argc = \count($argv);
    if ($argc < $arity) {
      return fn (...$more_argv) => \runtime\curry($fn, \array_merge($argv, $more_argv));
    } else if ($argc === $arity) {
      $result = $fn(...$argv);
      return \is_callable($result) ? \runtime\curry($result, []) : $result;
    } else {
      return \runtime\curry($fn(...\array_splice($argv, 0, $arity)), $argv);
    }
  }
}

namespace Kernel\Types {
  abstract class Maybe {}

  class Just extends \Kernel\Types\Maybe {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class None extends \Kernel\Types\Maybe {}
}

namespace Fmt {
  // #[inline]
  function int($i) {
    return (string)$i;
  }
}

namespace pipe_2 {
  function map($f, $m) {
    if ($m instanceof \Kernel\Types\Just) {
      $n = $m->{0};
      $a = new \Kernel\Types\Just(\runtime\curry($f, [ $n ]));
    } else if ($m instanceof \Kernel\Types\None) {
      $a = new \Kernel\Types\None();
    }
    return $a;
  }

  function or_else($fallback, $m) {
    if ($m instanceof \Kernel\Types\Just) {
      $_a = $m->{0};
      $a = $_a;
    } else if ($m instanceof \Kernel\Types\None) {
      $a = $fallback;
    }
    return $a;
  }

  // #[entry]
  function main() {
    print(\pipe_2\or_else("nothing", \pipe_2\map('\Fmt\int', new \Kernel\Types\Just(123))) . "\n");
  }
}

namespace {
  \pipe_2\main();
}
