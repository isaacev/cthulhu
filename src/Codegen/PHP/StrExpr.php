<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class StrExpr extends Expr {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  public function write(Writer $writer): Writer {
    return $writer->str($this->value);
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrExpr',
      'value' => $this->value
    ];
  }
}
