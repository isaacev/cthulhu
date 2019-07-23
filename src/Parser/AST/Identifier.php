<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class Identifier extends Expression {
  public $from;
  public $name;

  function __construct(Point $from, string $name) {
    $this->from = $from;
    $this->name = $name;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
  }

  public function jsonSerialize() {
    return [
      "type" => "Identifier",
      "name" => $this->name
    ];
  }
}
