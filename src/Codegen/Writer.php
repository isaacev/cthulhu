<?php

namespace Cthulhu\Codegen;

interface Writer {
  public function write(string $str): void;
  public function increase_indentation(): void;
  public function decrease_indentation(): void;
  public function get_indentation(): string;
}
