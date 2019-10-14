<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Source\Span $span, Expr $callee, array $polys, array $args) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->polys = $polys;
    $this->args = $args;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('CallExpr', $visitor_table)) {
      $visitor_table['CallExpr']($this);
    }

    $this->callee->visit($visitor_table);
    foreach ($this->polys as $poly) {
      $poly->visit($visitor_table);
    }
    foreach ($this->args as $arg) {
      $arg->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'CallExpr',
      'callee' => $this->callee,
      'polys' => $this->polys,
      'args' => $this->args,
    ];
  }
}
