<?php

namespace Cthulhu\lib\diff;

abstract class Line {
  protected $text;

  function __construct(string $text) {
    $this->text = $text;
  }

  function text(): string {
    return $this->text;
  }
}
