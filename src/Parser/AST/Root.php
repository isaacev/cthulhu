<?php

namespace Cthulhu\Parser\AST;

class Root implements \JsonSerializable {
  public $statements;

  function __construct(Block $statements) {
    $this->statements = $statements;
  }

  public function jsonSerialize() {
    return [
      'type' => 'Root',
      'statements' => $this->statements->jsonSerialize()
    ];
  }
}
