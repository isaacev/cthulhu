<?php

namespace Cthulhu\Parser\AST;

class Root implements \JsonSerializable {
  public $statements;

  function __construct(array $statements) {
    $this->statements = $statements;
  }

  public function jsonSerialize() {
    return [
      'type' => 'Root',
      'statements' => array_map(function ($stmt) {
        return $stmt->jsonSerialize();
      }, $this->statements)
    ];
  }
}
