<?php

namespace Cthulhu\lib\diff;

abstract class Line {
  protected string $text;

  public function __construct(string $text) {
    $this->text = $text;
  }

  public function text(): string {
    return $this->text;
  }
}
