<?php

namespace Cthulhu\Codegen\PHP;

abstract class Expr extends Node {
  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('Expr', $table)) {
      $table['Expr']($this);
    }
  }

  public function precedence(): int {
    return PHP_INT_MAX;
  }
}
