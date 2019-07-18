<?php

namespace Cthulhu\Parser\Lexer;

class Point {
  public $line;
  public $column;
  public $offset;

  function __construct(int $line, int $column, int $offset) {
    $this->line = $line;
    $this->column = $column;
    $this->offset = $offset;
  }

  public function next(string $char): Point {
    if ($char === "\n") {
      return new Point($this->line + 1, 1, $this->offset + 1);
    } else {
      return new Point($this->line, $this->column + 1, $this->offset + 1);
    }
  }

  public function __toString() {
    return "($this->line:$this->column)";
  }

  public static function first(): Point {
    return new Point(1, 1, 0);
  }
}
