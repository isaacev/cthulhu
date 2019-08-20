<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class IfStmt extends Stmt {
  public $cond;
  public $if_block;
  public $else_block;

  function __construct(Expr $cond, BlockNode $if_block, ?BlockNode $else_block) {
    $this->cond = $cond;
    $this->if_block = $if_block;
    $this->else_block = $else_block;
  }

  public function build(): Builder {
    $else_block = $this->else_block
      ? (new Builder)->keyword('else')->indented_block($this->else_block)
      : (new Builder);

    return (new Builder)
      ->keyword('if')
      ->paren_left()
      ->expr($this->cond)
      ->paren_right()
      ->indented_block($this->if_block)
      ->then($else_block);
  }

  public function jsonSerialize() {
    return [
      'type' => 'IfStmt',
      'cond' => $this->cond->jsonSerialize()
    ];
  }
}
