<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ast\nodes\Pattern;
use Cthulhu\lib\fmt\Buildable;

class Arm implements Buildable {
  public Pattern $pattern;
  public Expr $handler;

  public function __construct(Pattern $pattern, Expr $handler) {
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('case')
      ->space()
      ->pattern($this->pattern)
      ->space()
      ->then($this->handler)
      ->paren_right();
  }
}
