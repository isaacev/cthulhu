<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    $this->callee = $callee;
    $this->args = $args;
  }

  public function precedence(): int {
    return 40;
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->callee, $this->precedence())
      ->paren_left()
      ->each($this->args, (new Builder)->comma())
      ->paren_right();
  }

  public function jsonSerialize() {
    return [
      'type' => 'CallExpr'
    ];
  }
}