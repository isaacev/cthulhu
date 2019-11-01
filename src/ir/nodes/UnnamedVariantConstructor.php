<?php

namespace Cthulhu\ir\nodes;

class UnnamedVariantConstructor extends VariantConstructor {
  public $members;

  function __construct(Ref $ref, array $members) {
    parent::__construct($ref);
    $this->members = $members;
  }

  function children(): array {
    return array_merge(
      [ $this->ref ],
      $this->members
    );
  }
}
