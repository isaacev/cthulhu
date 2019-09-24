<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NullLiteral extends Expr {
  public function build(): Builder {
    return (new Builder)
      ->null_literal();
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('NullLiteral', $table)) {
      $table['NullLiteral']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'NullLiteral'
    ];
  }
}
