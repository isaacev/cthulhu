<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Source;

class Scanner {
  public static function from_file(Source\File $file): self {
    return new self($file);
  }

  private $chars;
  private $point;
  private $buffer;
  private $offset;

  function __construct(Source\File $file) {
    $this->chars = preg_split('//u', $file->contents, -1, PREG_SPLIT_NO_EMPTY);
    $this->point = new Source\Point($file);
    $this->buffer = null;
    $this->offset = 0;
  }

  private function is_done(): bool {
    return $this->offset >= count($this->chars);
  }

  public function peek(): Character {
    if ($this->buffer === null) {
      $this->buffer = $this->next();
    }

    return $this->buffer;
  }

  public function next(): Character {
    if ($this->buffer !== null) {
      $buf = $this->buffer;
      $this->buffer = null;
      return $buf;
    }

    if ($this->is_done()) {
      return new Character('', $this->point);
    }

    $char = $this->chars[$this->offset++];
    $point = $this->point;
    $this->point = $this->point->next($char);
    return new Character($char, $point);
  }
}
