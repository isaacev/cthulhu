<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class IdentExpr extends Expr {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function write(Writer $writer): Writer {
    return $writer->variable($this->name);
  }

  public function jsonSerialize() {
    return [
      'type' => 'IdentExpr',
      'name' => $this->name
    ];
  }
}
