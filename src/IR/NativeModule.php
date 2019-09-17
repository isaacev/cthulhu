<?php

namespace Cthulhu\IR;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\Codegen\PHP;
use Cthulhu\Types;

class NativeModule implements Buildable {
  public $scope;
  public $stmts;

  function __construct(string $name) {
    $this->scope = new ModuleScope(null, $name);
    $this->stmts = [];
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }

  public function fn(Symbol $symbol, Types\FnType $signature, PHP\Stmt $stmt): void {
    $this->scope->add($symbol, $signature);
    $this->stmts[] = $stmt;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('namespace')
      ->space()
      ->identifier($this->scope->symbol->name)
      ->space()
      ->brace_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->each($this->stmts)
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }
}
