<?php

namespace Cthulhu\lib\fmt;

class StringFormatter extends Formatter {
  protected string $buffer = '';

  protected function write(string $str): void {
    $this->buffer .= $str;
  }

  protected function use_color(): bool {
    return false;
  }

  public function __toString(): string {
    return $this->buffer;
  }
}
