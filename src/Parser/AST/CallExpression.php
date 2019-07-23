<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class CallExpression extends Expression {
  public $callee;
  public $arguments;

  function __construct(Expression $callee, array $arguments) {
    $this->callee = $callee;
    $this->arguments = $arguments;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->callee->from();
  }

  public function jsonSerialize() {
    $args = array_map(function ($arg) {
      return $arg->jsonSerialize();
    }, $this->arguments);

    return [
      'type' => 'CallExpression',
      'callee' => $this->callee->jsonSerialize(),
      'arguments' => $args
    ];
  }
}
