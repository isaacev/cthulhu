<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class FuncStmt extends Stmt {
  public $name;
  public $args;
  public $stmts;

  function __construct(string $name, array $args, array $stmts) {
    $this->name = $name;
    $this->args = $args;
    $this->stmts = $stmts;
  }

  public function write(Writer $writer): Writer {
    return $writer->keyword('function')
                  ->name($this->name)
                  ->paren_left()
                  // TODO
                  ->paren_right()
                  ->brace_left()
                  // TODO
                  ->brace_right();
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncStmt',
      'name' => $this->name,
      'args' => $this->args,
      'stmts' => array_map(function ($stmt) {
        return $stmt->jsonSerialize();
      }, $this->stmts)
    ];
  }
}
