<?php

namespace Call_1 {
  function hello() {
    print("hello\n");
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
