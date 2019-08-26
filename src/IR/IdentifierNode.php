<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class IdentifierNode extends Node {
  public $name;
  public $symbol;

  function __construct(string $name, Symbol $symbol) {
    $this->name = $name;
    $this->symbol = $symbol;
  }

  public function equals(IdentifierNode $other): bool {
    return $this->name === $other->name && $this->symbol->equals($other->symbol);
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'IdentifierNode',
      'symbol' => $this->symbol->jsonSerialize(),
      'name' => $this->name
    ];
  }
}
