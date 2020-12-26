<?php

namespace Call_2 {
  function hello($name) {
    return function() use ($name) {
      print($name . "\n");
      print("world\n");
    };
  }

  function main() {
    \Call_2\hello("foo")(null);
    return null;
  }
}

namespace {
  \Call_2\main(null);
}
