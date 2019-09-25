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

  public function codegen(Renamer $renamer): PHP\NamespaceNode {
    $ref = $renamer->get_reference($this->scope->symbol);
    $stmt_nodes = [];
    foreach ($this->stmts as list($symbol, $callable)) {
      $stmt_nodes[] = $callable($renamer, $symbol);
    }
    $block = new PHP\BlockNode($stmt_nodes);
    return new PHP\NamespaceNode($ref, $block);
  }
}
