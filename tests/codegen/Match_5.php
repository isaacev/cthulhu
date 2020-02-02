<?php

namespace match_5 {
  abstract class Shape {}

  class UnitCircle extends \match_5\Shape {}

  class Circle extends \match_5\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Square extends \match_5\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Rect extends \match_5\Shape {
    public $width;
    public $height;
    function __construct($args) {
      $this->width = $args["width"];
      $this->height = $args["height"];
    }
  }

  function describe($sh) {
    if ($sh instanceof \match_5\UnitCircle) {
      $a = "unit circle";
    } else if ($sh instanceof \match_5\Circle) {
      $r = $sh->{0};
      $a = "circle with radius " . (string)$r;
    } else if ($sh instanceof \match_5\Square) {
      $s = $sh->{0};
      $a = "square with perimeter " . (string)4.0 * $s;
    } else if ($sh instanceof \match_5\Rect) {
      $w = $sh->width;
      $h = $sh->height;
      $a = "rectangle with area " . (string)$w * $h;
    }
    print($a . "\n");
  }

  // #[entry]
  function main() {
    \match_5\describe(new \match_5\UnitCircle());
    \match_5\describe(new \match_5\Circle(2.0));
    \match_5\describe(new \match_5\Square(2.5));
    \match_5\describe(new \match_5\Rect([
      "width" => 5.5,
      "height" => 1.2
    ]));
  }
}

namespace {
  \match_5\main();
}
