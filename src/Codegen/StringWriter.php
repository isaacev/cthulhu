<?php

namespace Cthulhu\Codegen;

class StringWriter implements Writer {
  private $buffer = '';
  private $indent_level = 0;
  private $indent_unit = '  ';

  public function write(string $str): void {
    $this->buffer .= $str;
  }

  public function increase_indentation(): void {
    $this->indent_level++;
  }

  public function decrease_indentation(): void {
    $this->indent_level--;
  }

  public function get_indentation(): string {
    return str_repeat($this->indent_unit, $this->indent_level);
  }

  public function __toString(): string {
    return $this->buffer;
  }
}
