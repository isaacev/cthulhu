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

namespace Record_4 {
  function main() {
    $x = [
      "square" => function($a, $b) {
        return $a * $b;
      }
    ];
    print((string)\runtime\curry($x["square"], [
      2,
      3
    ]) . "\n");
    return null;
  }
}

namespace {
  \Record_4\main(null);
  return null;
}
