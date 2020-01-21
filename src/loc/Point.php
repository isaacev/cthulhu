<?php

namespace Cthulhu\loc;

class Point implements Spanlike {
  public File $file;
  public int $line;
  public int $column;

  public function __construct(File $file, int $line, int $column) {
    assert($line >= 1);
    assert($column >= 1);
    $this->file   = $file;
    $this->line   = $line;
    $this->column = $column;
  }

  public function span(): Span {
    return new Span($this, $this->next_column());
  }

  public function from(): Point {
    return $this;
  }

  public function to(): Point {
    return $this->next_column();
  }

  public function prev_column(): self {
    return new self($this->file, $this->line, max(1, $this->column - 1));
  }

  public function next_column(): self {
    return new self($this->file, $this->line, $this->column + 1);
  }

  public function next_line(): self {
    return new self($this->file, $this->line + 1, 1);
  }

  public function eq(self $other): bool {
    return (
      $this->file === $other->file &&
      $this->line === $other->line &&
      $this->column === $other->column
    );
  }

  public function lt(self $other): bool {
    return (
      $this->file === $other->file && (
        $this->line < $other->line ||
        $this->column < $other->column
      )
    );
  }

  public function lte(self $other): bool {
    return $this->lt($other) || $this->eq($other);
  }

  public function __toString() {
    return "($this->line:$this->column)";
  }

  public function __debugInfo() {
    return [
      'filename' => $this->file->filepath,
      'line' => $this->line,
      'column' => $this->column,
    ];
  }
}
