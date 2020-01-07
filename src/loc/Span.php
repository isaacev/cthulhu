<?php

namespace Cthulhu\loc;

class Span implements Spanlike {
  public Point $from;
  public Point $to;

  public function __construct(Point $from, Point $to) {
    assert($from->lte($to));
    $this->from = $from;
    $this->to   = $to;
  }

  public function span(): Span {
    return $this;
  }

  public function from(): Point {
    return $this->from;
  }

  public function to(): Point {
    return $this->to;
  }

  public function __toString() {
    return "$this->from to $this->to";
  }

  public static function join(Spanlike $first, Spanlike ...$rest): Span {
    $last = empty($rest) ? $first : end($rest);
    return new Span($first->from(), $last->to());
  }
}
