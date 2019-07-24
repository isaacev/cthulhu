<?php

namespace Cthulhu\Parser\AST;

class Root implements \JsonSerializable {
  public $stmts;

  function __construct(array $stmts) {
    $this->stmts = $stmts;
  }

  public function jsonSerialize() {
    $stmts_json = array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->stmts);

    return [
      'type' => 'Root',
      'stmts' => $stmts_json
    ];
  }
}
