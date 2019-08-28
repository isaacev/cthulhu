<?php

namespace Cthulhu\IR;

use Cthulhu\Types;
use Cthulhu\Codegen\Builder;
use Cthulhu\Codegen\Buildable;

class NativeModule implements Buildable {
  public $scope;
  public $builders;

  function __construct(string $name) {
    $this->scope = new ModuleScope3(null, $name);
    $this->builders = [];
  }

  public function scope(): ModuleScope3 {
    return $this->scope;
  }

  public function fn(string $name, Types\FnType $signature, Builder $builder): void {
    $symbol = new Symbol3($name, $this->scope->symbol);
    $this->scope->add($symbol, $signature);
    $this->builders[] = $builder;
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
      ->each($this->builders)
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }
}
