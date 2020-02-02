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
    $b = $d;
    if ($b instanceof \Union_5\Left && $b->{0} == true) {
      $c = print("was true" . "\n");
    } else if ($b instanceof \Union_5\Left && $b->{0} == false) {
      $c = print("was false" . "\n");
    } else if ($b instanceof \Union_5\Right) {
      $msg = $b->{0};
      $c = print($msg . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $c;
    if (true) {
      $g = new \Union_5\Left(false);
    } else {
      $g = new \Union_5\Right("unable to generate a true value");
    }
    $e = $g;
    if ($e instanceof \Union_5\Left && $e->{0} == true) {
      $f = print("was true" . "\n");
    } else if ($e instanceof \Union_5\Left && $e->{0} == false) {
      $f = print("was false" . "\n");
    } else if ($e instanceof \Union_5\Right) {
      $_msg = $e->{0};
      $f = print($_msg . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $f;
  }
}

namespace {
  \Union_5\main(null);
}
