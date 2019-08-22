<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class BlockNode extends Node {
  public $scope;
  public $stmts;

  function __construct(BlockScope $scope, array $stmts) {
    $this->scope = $scope;
    $this->stmts = $stmts;
  }

  public function length(): int {
    return count($this->stmts);
  }

  public function last_stmt(): ?Stmt {
    if ($this->length() > 0) {
      return $this->stmts[$this->length() - 1];
    }
    return null;
  }

  public function type(): Types\Type {
    $last_stmt = $this->last_stmt();
    return $last_stmt ? $last_stmt->type() : new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'BlockNode',
      'stmts' => array_map(function ($stmt) {
        return $stmt->jsonSerialize();
      }, $this->stmts)
    ];
  }
}