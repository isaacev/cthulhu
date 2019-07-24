<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class NumExpr extends Expr {
  public $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  public function write(Writer $writer): Writer {
    return $writer->num($this->value);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumExpr',
      'value' => $this->value
    ];
  }
}
