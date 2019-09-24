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

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('FuncExpr', $table)) {
      $table['FuncExpr']($this);
    }

    foreach ($this->params as $param) { $param->visit($table); }
    $this->block->visit($table);
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
      ->then($this->block);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncExpr'
    ];
  }
}
