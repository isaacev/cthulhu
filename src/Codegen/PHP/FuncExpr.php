<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class FuncExpr extends Expr {
  public $args;
  public $stmts;

  function __construct(array $args, array $stmts) {
    $this->args = $args;
    $this->stmts = $stmts;
  }

  public function write(Writer $writer): Writer {
    return $writer->keyword('function')
                  ->paren_left()
                  // TODO
                  ->paren_right()
                  ->brace_left()
                  // TODO
                  ->brace_right();
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncExpr',
      'args' => $this->args,
      'stmts' => array_map(function ($stmt) {
        return $stmt->jsonSerialize();
      }, $this->stmts)
    ];
  }
}
