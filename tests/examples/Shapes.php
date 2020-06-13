<?php

namespace _List {
  function iter($b, $c) {
    while (true) {
      $callback = $b;
      $inputs = $c;
      if (\count($inputs) == 0) {
        return null;
      } else if (\count($inputs) >= 1) {
        $first = $inputs[0];
        $rest = \array_slice($inputs, 1);
        $callback($first);
        $b = $callback;
        $c = $rest;
        continue;
      } else {
        die("match expression did not cover all possibilities\n");
      }
    }
  }
}

namespace Shapes {
  abstract class Shape {}
  class UnitCircle extends \Shapes\Shape {
    function __construct() {
      // empty
    }
  }
  class Circle extends \Shapes\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Square extends \Shapes\Shape {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Rect extends \Shapes\Shape {
    public $width;
    public $height;
    function __construct($a) {
      $this->width = $a["width"];
      $this->height = $a["height"];
    }
  }
  function desc($sh) {
    if ($sh instanceof \Shapes\UnitCircle) {
      $a = "unit circle";
    } else if ($sh instanceof \Shapes\Circle && $sh->{0} == 1.0) {
      $a = "unit circle";
    } else if ($sh instanceof \Shapes\Circle) {
      $a = "circle";
    } else if ($sh instanceof \Shapes\Square) {
      $a = "square";
    } else if ($sh instanceof \Shapes\Rect) {
      $a = "rectangle";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    if ($sh instanceof \Shapes\UnitCircle) {
      $c = 3.14;
    } else if ($sh instanceof \Shapes\Circle && $sh->{0} == 1.0) {
      $c = 3.14;
    } else if ($sh instanceof \Shapes\Circle) {
      $r = $sh->{0};
      $c = 3.14 * $r * $r;
    } else if ($sh instanceof \Shapes\Square) {
      $s = $sh->{0};
      $c = $s * $s;
    } else if ($sh instanceof \Shapes\Rect) {
      $w = $sh->width;
      $h = $sh->height;
      $c = $w * $h;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($a . " has an area of " . (string)$c . "\n");
    return null;
  }

  function main() {
    $u = new \Shapes\UnitCircle();
    $v = new \Shapes\Circle(1.0);
    $c = new \Shapes\Circle(5.0);
    $s = new \Shapes\Square(3.0);
    $r = new \Shapes\Rect([
      "width" => 4.0,
      "height" => 3.0
    ]);
    \_List\iter('\Shapes\desc', [
      $u,
      $v,
      $c,
      $s,
      $r
    ]);
    return null;
  }
}

namespace {
  \Shapes\main(null);
}
