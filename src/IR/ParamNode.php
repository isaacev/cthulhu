<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class ParamNode extends Node {
  public $identifier;
  public $symbol;

  function __construct(IdentifierNode $ident, Symbol $symbol) {
    $this->identifier = $ident;
    $this->symbol = $symbol;
  }

  public function type(): Type {
    return $this->symbol->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ParamNode',
      'identifier' => $this->identifier,
      'symbol' => $this->symbol->jsonSerialize()
    ];
  }
}
