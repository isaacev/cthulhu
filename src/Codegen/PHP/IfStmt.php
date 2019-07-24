<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class IfStmt extends Stmt {
  public $cond;
  public $if_stmts;
  public $else_stmts;

  function __constructor(PHP\Expr $cond, array $if_stmts, ?array $else_stmts) {
    $this->cond = $cond;
    $this->if_stmts = $if_stmts;
    $this->else_stmts = $else_stmts;
  }

  public function write(Writer $writer): Writer {
    return $writer->keyword('if')
                  ->paren_left()
                  ->node($this->cond)
                  ->paren_right()
                  ->brace_left()
                  // TODO
                  ->brace_right();
  }

  public function jsonSerialize() {
    return [
      'type' => 'IfStmt',
      'cond' => $this->cond->jsonSerialize(),
      'if_stmts' => array_map(function ($stmt) { return $stmt->jsonSerialize(); }, $this->if_stmts),
      'else_stmts' => $this->else_stmts ? array_map(function ($stmt) { return $stmt->jsonSerialize(); }, $this->else_stmts) : null
    ];
  }
}
