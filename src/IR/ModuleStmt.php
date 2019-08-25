<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class ModuleStmt extends Stmt {
  public $name;
  public $scope;
  public $block;

  function __construct(string $name, ModuleScope $scope, BlockNode $block) {
    $this->name = $name;
    $this->scope = $scope;
    $this->block = $block;
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ModuleStmt',
      'name' => $this->name,
      'block' => $this->block->jsonSerialize()
    ];
  }
}
