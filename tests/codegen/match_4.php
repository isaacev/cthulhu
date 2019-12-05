<?php

namespace match_4 {
  abstract class Shape {}

  class UnitCircle extends \match_4\Shape {}

  class Circle extends \match_4\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Square extends \match_4\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Rect extends \match_4\Shape {
    public $width;
    public $height;
    function __construct($args) {
      $this->width = $args["width"];
      $this->height = $args["height"];
    }
  }

  function describe($sh) {
    if ($sh instanceof \match_4\UnitCircle) {
      $a = "unit circle";
    } else if ($sh instanceof \match_4\Circle) {
      $a = "circle";
    } else if ($sh instanceof \match_4\Square) {
      $a = "square";
    } else if ($sh instanceof \match_4\Rect) {
      $a = "rectangle";
    }
    print($a . "\n");
  }

  // #[entry]
  function main() {
    \match_4\describe(new \match_4\UnitCircle());
    \match_4\describe(new \match_4\Circle(2.0));
    \match_4\describe(new \match_4\Square(2.5));
    \match_4\describe(new \match_4\Rect([
      "width" => 5.5,
      "height" => 1.2
    ]));
  }
}

namespace {
  \match_4\main();
}
