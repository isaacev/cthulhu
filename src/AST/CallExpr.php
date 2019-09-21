<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Source\Span $span, Expr $callee, array $args) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->args = $args;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('CallExpr', $visitor_table)) {
      $visitor_table['CallExpr']($this);
    }

    $this->callee->visit($visitor_table);
    foreach ($this->args as $arg) {
      $arg->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    $args = array_map(function ($arg) {
      return $arg->jsonSerialize();
    }, $this->args);

    return [
      'type' => 'CallExpr',
      'callee' => $this->callee->jsonSerialize(),
      'args' => $args
    ];
  }
}
