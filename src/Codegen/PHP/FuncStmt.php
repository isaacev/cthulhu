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

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('FuncStmt', $table)) {
      $table['FuncStmt']($this);
    }

    $this->body->visit($table);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)->comma())
      ->paren_right()
      ->space()
      ->then($this->body);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncStmt',
      'name' => $this->name
    ];
  }
}
