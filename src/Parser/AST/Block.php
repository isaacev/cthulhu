<?php

namespace Cthulhu\Parser\AST;

class Block implements \JsonSerializable {
  public $statements;

  function __construct(array $statements) {
    $this->statements = $statements;
  }

  public function jsonSerialize() {
    return array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->statements);
  }
}
