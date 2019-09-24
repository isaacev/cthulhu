<?php

namespace Cthulhu\Codegen\PHP;

abstract class Stmt extends Node {
  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('Stmt', $table)) {
      $table['Stmt']($this);
    }
  }
}
