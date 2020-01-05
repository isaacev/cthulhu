<?php

namespace Cthulhu\ir\arity;

class StaticArity extends KnownArity {
  public function __construct(int $params, Arity $returns) {
    assert($params > 0);
    parent::__construct($params, $returns);
  }

  public function equals(Arity $other): bool {
    if ($other instanceof self) {
      return (
        $this->params === $other->params &&
        $this->returns->equals($other->returns)
      );
    }
    return false;
  }

  public function __toString(): string {
    return "static($this->params) -> $this->returns";
  }
}
