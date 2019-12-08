<?php

namespace Cthulhu\Source;

class Point {
  public File $file;
  public int $line;
  public int $column;

  function __construct(File $file, int $line = 1, int $column = 1) {
    $this->file   = $file;
    $this->line   = $line;
    $this->column = $column;
  }

  public function next(string $char = ''): Point {
    if ($char === "\n") {
      return new Point($this->file, $this->line + 1, 1);
    } else {
      return new Point($this->file, $this->line, $this->column + 1);
    }
  }

  public function to_span(): Span {
    return new Span($this, $this->next());
  }

  public function __toString() {
    return "($this->line:$this->column)";
  }
}
