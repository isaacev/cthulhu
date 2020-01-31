<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\types\Type;

abstract class FormPattern extends Pattern {
  public RefSymbol $ref_symbol;

  public function __construct(Type $type, RefSymbol $ref_symbol) {
    parent::__construct($type);
    $this->ref_symbol = $ref_symbol;
  }
}
