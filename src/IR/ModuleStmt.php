<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class ModuleStmt extends Stmt {
  public $identifier;
  public $scope;
  public $block;

  function __construct(IdentifierNode $ident, ModuleScope $scope, BlockNode $block) {
    $this->identifier = $ident;
    $this->scope = $scope;
    $this->block = $block;
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ModuleStmt',
      'identifier' => $this->identifier,
      'scope' => $this->scope->jsonSerialize(),
      'block' => $this->block->jsonSerialize()
    ];
  }
}
