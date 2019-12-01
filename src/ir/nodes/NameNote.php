<?php

namespace Cthulhu\ir\nodes;

class NameNote extends Note {
  public Ref $ref;

  function __construct(Ref $ref) {
    parent::__construct();
    $this->ref = $ref;
  }

  function children(): array {
    return [ $this->ref ];
  }
}
