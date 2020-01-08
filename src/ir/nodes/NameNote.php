<?php

namespace Cthulhu\ir\nodes;

class NameNote extends Note {
  public Ref $ref;

  public function __construct(Ref $ref) {
    parent::__construct();
    $this->ref = $ref;
  }

  public function children(): array {
    return [ $this->ref ];
  }
}
