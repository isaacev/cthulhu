<?php

namespace Match_4 {
  abstract class Shape {}
  class UnitCircle extends \Match_4\Shape {
    function __construct() {
      // empty
    }
  }
  class Circle extends \Match_4\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Square extends \Match_4\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Rect extends \Match_4\Shape {
    public $width;
    public $height;
    function __construct($a) {
      $this->width = $a["width"];
      $this->height = $a["height"];
    }
  }
  function main() {
    $b = new \Match_4\UnitCircle();
    if ($b instanceof \Match_4\UnitCircle) {
      $a = "unit circle";
    } else if ($b instanceof \Match_4\Circle) {
      $a = "circle";
    } else if ($b instanceof \Match_4\Square) {
      $a = "square";
    } else if ($b instanceof \Match_4\Rect) {
      $a = "rectangle";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($a . "\n");
    $d = new \Match_4\Circle(2.0);
    if ($d instanceof \Match_4\UnitCircle) {
      $c = "unit circle";
    } else if ($d instanceof \Match_4\Circle) {
      $c = "circle";
    } else if ($d instanceof \Match_4\Square) {
      $c = "square";
    } else if ($d instanceof \Match_4\Rect) {
      $c = "rectangle";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($c . "\n");
    $f = new \Match_4\Square(2.5);
    if ($f instanceof \Match_4\UnitCircle) {
      $e = "unit circle";
    } else if ($f instanceof \Match_4\Circle) {
      $e = "circle";
    } else if ($f instanceof \Match_4\Square) {
      $e = "square";
    } else if ($f instanceof \Match_4\Rect) {
      $e = "rectangle";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($e . "\n");
    $h = new \Match_4\Rect([
      "width" => 5.5,
      "height" => 1.2
    ]);
    if ($h instanceof \Match_4\UnitCircle) {
      $g = "unit circle";
    } else if ($h instanceof \Match_4\Circle) {
      $g = "circle";
    } else if ($h instanceof \Match_4\Square) {
      $g = "square";
    } else if ($h instanceof \Match_4\Rect) {
      $g = "rectangle";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($g . "\n");
    return null;
  }
}

namespace {
  \Match_4\main(null);
}
