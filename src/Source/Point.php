<?php

namespace Cthulhu\Source;

class Point {
  public $line;
  public $column;

  function __construct(int $line = 1, int $column = 1) {
    $this->line = $line;
    $this->column = $column;
  }

  public function next(string $char = ''): Point {
    if ($char === "\n") {
      return new Point($this->line + 1, 1);
    } else {
      return new Point($this->line, $this->column + 1);
    }
  }

  public function to_span(): Span {
    return new Span($this, $this->next());
  }

  public function __toString() {
    return "($this->line:$this->column)";
  }
}
