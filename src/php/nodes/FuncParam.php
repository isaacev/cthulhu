<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FuncParam extends Node {
  public bool $is_variadic;
  public Variable $variable;

  function __construct(bool $is_variadic, Variable $variable) {
    parent::__construct();
    $this->is_variadic = $is_variadic;
    $this->variable    = $variable;
  }

  public function to_children(): array {
    return [ $this->variable ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->is_variadic, $nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->maybe($this->is_variadic, (new Builder)
        ->operator('...'))
      ->then($this->variable);
  }

  static function from_var(Variable $variable) {
    return new self(false, $variable);
  }
}
