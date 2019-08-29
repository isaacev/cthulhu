<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Span;

class Snippet {
  public $program;
  public $location;
  public $options;

  function __construct(string $program, Span $location, array $options = []) {
    $this->program = $program;
    $this->location = $location;
    $this->options = $options;
  }

  public function get_option(string $name, $fallback) {
    if (array_key_exists($name, $this->options)) {
      return $this->options[$name];
    } else {
      return $fallback;
    }
  }
}
