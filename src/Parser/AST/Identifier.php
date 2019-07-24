<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class Identifier extends Expression {
  public $name;

  function __construct(Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function jsonSerialize() {
    return [
      "type" => "Identifier",
      "name" => $this->name
    ];
  }
}
