<?php

namespace Match_5 {
  abstract class Shape {}
  class UnitCircle extends \Match_5\Shape {
    function __construct() {
      // empty
    }
  }
  class Circle extends \Match_5\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Square extends \Match_5\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Rect extends \Match_5\Shape {
    public $width;
    public $height;
    function __construct($a) {
      $this->width = $a["width"];
      $this->height = $a["height"];
    }
  }
  function main() {
    $b = new \Match_5\UnitCircle();
    if ($b instanceof \Match_5\UnitCircle) {
      $a = "unit circle";
    } else if ($b instanceof \Match_5\Circle) {
      $r = $b->{0};
      $a = "circle with radius " . (string)$r;
    } else if ($b instanceof \Match_5\Square) {
      $s = $b->{0};
      $a = "square with perimeter " . (string)(4.0 * $s);
    } else if ($b instanceof \Match_5\Rect) {
      $w = $b->width;
      $h = $b->height;
      $a = "rectangle with area " . (string)($w * $h);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($a . "\n");
    $d = new \Match_5\Circle(2.0);
    if ($d instanceof \Match_5\UnitCircle) {
      $c = "unit circle";
    } else if ($d instanceof \Match_5\Circle) {
      $r = $d->{0};
      $c = "circle with radius " . (string)$r;
    } else if ($d instanceof \Match_5\Square) {
      $s = $d->{0};
      $c = "square with perimeter " . (string)(4.0 * $s);
    } else if ($d instanceof \Match_5\Rect) {
      $w = $d->width;
      $h = $d->height;
      $c = "rectangle with area " . (string)($w * $h);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($c . "\n");
    $f = new \Match_5\Square(2.5);
    if ($f instanceof \Match_5\UnitCircle) {
      $e = "unit circle";
    } else if ($f instanceof \Match_5\Circle) {
      $r = $f->{0};
      $e = "circle with radius " . (string)$r;
    } else if ($f instanceof \Match_5\Square) {
      $s = $f->{0};
      $e = "square with perimeter " . (string)(4.0 * $s);
    } else if ($f instanceof \Match_5\Rect) {
      $w = $f->width;
      $h = $f->height;
      $e = "rectangle with area " . (string)($w * $h);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($e . "\n");
    $i = new \Match_5\Rect([
      "width" => 5.5,
      "height" => 1.2
    ]);
    if ($i instanceof \Match_5\UnitCircle) {
      $g = "unit circle";
    } else if ($i instanceof \Match_5\Circle) {
      $r = $i->{0};
      $g = "circle with radius " . (string)$r;
    } else if ($i instanceof \Match_5\Square) {
      $s = $i->{0};
      $g = "square with perimeter " . (string)(4.0 * $s);
    } else if ($i instanceof \Match_5\Rect) {
      $w = $i->width;
      $h = $i->height;
      $g = "rectangle with area " . (string)($w * $h);
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($g . "\n");
    return null;
  }
}

namespace {
  \Match_5\main(null);
}
