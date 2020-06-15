<?php

namespace Record_1 {
  function main() {
    $x = [
      "name" => "foo",
      "kind" => "bar"
    ];
    print($x["name"] . "\n");
    $y = [
      "name" => "foo",
      "kind" => "bar"
    ];
    print($y["name"] . "\n");
    return null;
  }
}

namespace {
  \Record_1\main(null);
}
