<?php

namespace Union_5 {
  abstract class Result {}
  class Left extends \Union_5\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Right extends \Union_5\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  function main() {
    if (true) {
      $d = new \Union_5\Left(true);
    } else {
      $d = new \Union_5\Right("unable to generate a true value");
    }
    if ($d instanceof \Union_5\Left && $d->{0} == true) {
      print("was true" . "\n");
    } else if ($d instanceof \Union_5\Left && $d->{0} == false) {
      print("was false" . "\n");
    } else if ($d instanceof \Union_5\Right) {
      $msg = $d->{0};
      print($msg . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    if (true) {
      $g = new \Union_5\Left(false);
    } else {
      $g = new \Union_5\Right("unable to generate a true value");
    }
    if ($g instanceof \Union_5\Left && $g->{0} == true) {
      print("was true" . "\n");
    } else if ($g instanceof \Union_5\Left && $g->{0} == false) {
      print("was false" . "\n");
    } else if ($g instanceof \Union_5\Right) {
      $_msg = $g->{0};
      print($_msg . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \Union_5\main(null);
}
