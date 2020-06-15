<?php

namespace Record_2 {
  function main() {
    $x = [
      "name" => "foo"
    ];
    print("name: " . $x["name"] . "\n");
    $y = [
      "name" => "bar"
    ];
    print("name: " . $y["name"] . "\n");
    return null;
  }
}

namespace {
  \Record_2\main(null);
}
