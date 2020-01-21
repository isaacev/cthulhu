<?php

namespace Cthulhu\ir\types\hm;

class Func extends TypeOper {
  public Type $input;
  public Type $output;

  public function __construct(Type $input, Type $output) {
    parent::__construct('Func', [ $input, $output ]);
    $this->input  = $input;
    $this->output = $output;
  }

  public function fresh(callable $fresh_rec): self {
    return new self(
      $fresh_rec($this->input),
      $fresh_rec($this->output)
    );
  }

  public function max_arguments(): int {
    if ($this->output instanceof Func) {
      return 1 + $this->output->max_arguments();
    } else {
      return 1;
    }
  }

  public function __toString(): string {
    if (self::is_func($this->input)) {
      return "($this->input) → $this->output";
    }
    return "$this->input → $this->output";
  }

  public static function is_func(Type $t): bool {
    if ($t instanceof TypeVar) {
      return $t->instance ? self::is_func($t->instance) : false;
    }
    return $t instanceof self;
  }
}
