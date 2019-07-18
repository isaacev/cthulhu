<?php

namespace Cthulhu\Parser\Lexer;

class Scanner {
  private $text;
  private $chars;
  private $point;
  private $buffer;

  function __construct($text) {
    $this->text = $text;
    $this->chars = preg_split('//u', $this->text, -1, PREG_SPLIT_NO_EMPTY);
    $this->point = Point::first();
    $this->buffer = null;
  }

  private function is_done(): bool {
    return $this->point->offset >= count($this->chars);
  }

  public function peek(): ?Character {
    if ($this->buffer === null) {
      $this->buffer = $this->next();
    }

    return $this->buffer;
  }

  public function next() {
    if ($this->buffer !== null) {
      $buf = $this->buffer;
      $this->buffer = null;
      return $buf;
    }

    if ($this->is_done()) {
      return null;
    }

    $char = $this->chars[$this->point->offset];
    $point = $this->point;
    $this->point = $this->point->next($char);
    return new Character($char, $point);
  }
}
