<?php

namespace Cthulhu\types;

class Func extends Oper {
  public Type $input;
  public Type $output;

  public function __construct(Type $input, Type $output) {
    parent::__construct('Func', [ $input, $output ]);
    $this->input  = $input;
    $this->output = $output;
  }

  public function __toString(): string {
    if (self::is_func($this->input)) {
      return "($this->input) → $this->output";
    }
    return "$this->input → $this->output";
  }

  public static function is_func(Type $t): bool {
    if ($t instanceof Variable) {
      return $t->instance ? self::is_func($t->instance) : false;
    }
    return $t instanceof self;
  }
}
