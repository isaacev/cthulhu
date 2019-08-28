<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class FuncStmt extends Stmt {
  public $name;
  public $params;
  public $body;

  function __construct(Reference $name, array $params, BlockNode $body) {
    $this->name = $name;
    $this->params = $params;
    $this->body = $body;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)->comma())
      ->paren_right()
      ->then($this->body);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncStmt',
      'name' => $this->name
    ];
  }
}
