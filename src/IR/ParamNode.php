<?php

namespace Cthulhu\IR;

class ParamNode extends Node {
  public $identifier;
  public $symbol;

  function __construct(IdentifierNode $ident, Symbol $symbol) {
    $this->identifier = $ident;
    $this->symbol     = $symbol;
  }
}
