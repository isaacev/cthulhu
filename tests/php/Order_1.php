<?php

namespace Order_1 {
  function main() {
    return print("ab\n");
  }
}

namespace {
  \Order_1\main(null);
}
