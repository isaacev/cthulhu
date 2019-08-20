<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class FuncExpr extends Expr {
  public $params;
  public $free_variables;
  public $block;

  function __construct(array $params, array $free_variables, BlockNode $block) {
    $this->params = $params;
    $this->free_variables = $free_variables;
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->paren_left()
      ->each($this->params, (new Builder)->comma())
      ->paren_right()
      ->maybe(count($this->free_variables) > 0, (new Builder)
        ->keyword('use')
        ->paren_left()
        ->each($this->free_variables, (new Builder)
          ->comma()
          ->space())
        ->paren_right())
      ->indented_block($this->block);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncExpr'
    ];
  }
}
