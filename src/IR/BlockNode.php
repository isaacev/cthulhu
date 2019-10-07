<?php

namespace Cthulhu\IR;

class BlockNode extends Node {
  public $type;
  public $scope;
  public $stmts;

  function __construct(Types\Type $type, BlockScope $scope, array $stmts) {
    $this->type  = $type;
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

  public function return_type(): Types\Type {
    return $this->type;
  }
}
