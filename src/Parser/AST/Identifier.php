<?php

namespace Cthulhu\Parser\AST;

class Identifier extends Expression {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function jsonSerialize() {
    return [
      "type" => "Identifier",
      "name" => $this->name
    ];
  }
}
