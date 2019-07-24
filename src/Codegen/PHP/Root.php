<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class Root extends Node {
  public $stmts;

  function __construct(array $stmts) {
    $this->stmts = $stmts;
  }

  public function write(Writer $writer): Writer {
    return $writer->newline_separated($this->stmts);
  }

  public function jsonSerialize() {
    return [
      'type' => 'Root',
      'stmts' => array_map(function ($stmt) { return $stmt->jsonSerialize(); }, $this->stmts)
    ];
  }
}
