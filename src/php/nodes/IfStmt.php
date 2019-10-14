<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class IfStmt extends Stmt {
  public $cond;
  public $if_block;
  public $else_block;

  function __construct(Expr $cond, BlockNode $if_block, ?BlockNode $else_block) {
    $this->cond = $cond;
    $this->if_block = $if_block;
    $this->else_block = $else_block;
  }

  public function to_children(): array {
    if ($this->else_block) {
      return [
        $this->cond,
        $this->if_block,
        $this->else_block
      ];
    } else {
      return [
        $this->cond,
        $this->if_block
      ];
    }
  }

  public function from_children(array $nodes): Node {
    $else_block = count($nodes) >= 3 ? $nodes[2] : null;
    return new self($nodes[0], $nodes[1], $else_block);
  }

  public function build(): Builder {
    $else_block = $this->else_block
      ? (new Builder)
        ->space()
        ->keyword('else')
        ->space()
        ->then($this->else_block)
      : (new Builder);

    return (new Builder)
      ->keyword('if')
      ->space()
      ->paren_left()
      ->expr($this->cond)
      ->paren_right()
      ->space()
      ->then($this->if_block)
      ->then($else_block);
  }
}
