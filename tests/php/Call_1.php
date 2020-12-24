<?php

namespace Prelude {
  function plus_plus($a, $b) {
    return $a . $b;
  }
}

namespace Io {
  function _print($str) {
    return print($str);
  }

  function println($str) {
    return \Io\_print(\Prelude\plus_plus($str, "\n"));
  }
}

namespace Call_1 {
  function hello() {
    \Io\println("hello");
    return null;
  }

  function main() {
    \Call_1\hello(null);
    return null;
  }
}

namespace {
  \Call_1\main(null);
}
