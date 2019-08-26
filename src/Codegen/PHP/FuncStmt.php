<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class FuncStmt extends Stmt {
  public $name;
  public $params;
  public $block;

  function __construct(Identifier $name, array $params, BlockNode $block) {
    $this->name = $name;
    $this->params = $params;
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)->comma())
      ->paren_right()
      ->then($this->block);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncStmt',
      'name' => $this->name
    ];
  }
}
