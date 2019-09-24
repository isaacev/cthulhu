<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class BoolExpr extends Expr {
  public $value;

  function __construct(bool $value) {
    $this->value = $value;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('BoolExpr', $table)) {
      $table['BoolExpr']($this);
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->bool_literal($this->value);
  }

  public function jsonSerialize() {
    return [
      'type' => 'BoolExpr',
      'value' => $this->value
    ];
  }
}
