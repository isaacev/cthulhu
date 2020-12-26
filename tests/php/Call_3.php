<?php

namespace Call_3 {
  function main() {
    (function($name) {
      print($name . "\n");
      print("world\n");
    })("foo");
    return null;
  }
}

namespace {
  \Call_3\main(null);
}
