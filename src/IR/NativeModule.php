<?php

namespace Cthulhu\IR;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\Codegen\PHP;
use Cthulhu\Codegen\Renamer;
use Cthulhu\Types;

class NativeModule {
  public $scope;
  public $stmts;

  function __construct(string $name) {
    $this->scope = new ModuleScope(null, $name);
    $this->stmts = [];
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }

  public function fn(Symbol $symbol, Types\FnType $signature, callable $callable): void {
    $this->scope->add($symbol, $signature);
    $this->stmts[] = [$symbol, $callable];
  }

  public function build(Renamer $renamer): Builder {
    $stmts = [];
    foreach ($this->stmts as list($symbol, $callable)) {
      $stmts[] = $callable($renamer, $symbol);
    }

    return (new Builder)
      ->keyword('namespace')
      ->space()
      ->identifier($renamer->resolve($this->scope->symbol))
      ->space()
      ->brace_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->each($stmts)
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }
}
