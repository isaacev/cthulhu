<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class FuncExpr extends Expr {
  public $params;
  public $block;

  function __construct(array $params, BlockNode $block) {
    $this->params = $params;
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->paren_left()
      ->each($this->params, (new Builder)->comma())
      ->paren_right()
      ->indented_block($this->block);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncExpr'
    ];
  }
}
