<?php

namespace Record_3 {
  function main() {
    $x = [
      "square" => function($a) {
        return $a * $a;
      }
    ];
    print((string)$x["square"](2) . "\n");
    return null;
  }
}

namespace {
  \Record_3\main(null);
}
