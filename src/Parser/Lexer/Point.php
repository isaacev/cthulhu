<?php

namespace Cthulhu\Parser\Lexer;

class Point {
  public $line;
  public $column;
  public $offset;

  function __construct(int $line = 1, int $column = 1, int $offset = 0) {
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
}
