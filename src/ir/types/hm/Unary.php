<?php

namespace Cthulhu\ir\types\hm;

class Unary extends TypeOper {
  public Type $member;

  public function __construct(string $name, Type $member) {
    parent::__construct($name, [ $member ]);
    $this->member = $member;
  }

  public function fresh(callable $fresh_rec): Type {
    return new self($this->name, $fresh_rec($this->member));
  }
}
