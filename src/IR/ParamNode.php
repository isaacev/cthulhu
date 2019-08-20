<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class ParamNode extends Node {
  public $name;
  public $symbol;

  function __construct(string $name, Symbol $symbol) {
    $this->name = $name;
    $this->symbol = $symbol;
  }

  public function type(): Type {
    return $this->symbol->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ParamNode',
      'name' => $this->name,
      'symbol' => $this->symbol->jsonSerialize()
    ];
  }
}
