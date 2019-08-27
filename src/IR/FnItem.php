<?php

namespace Cthulhu\IR;

use Cthulhu\Types\FnType;

class FnItem extends Item {
  public $symbol;
  public $signature;
  public $scope;
  public $block;

  function __construct(Symbol3 $symbol, FnType $signature, BlockScope3 $scope, BlockNode $block) {
    $this->symbol = $symbol;
    $this->signature = $signature;
    $this->scope = $scope;
    $this->block = $block;
  }

  public function jsonSerialize() {
    return [
      'type' => 'FnItem',
      'symbol' => $this->symbol,
      'signature' => $this->signature,
      'block' => $this->block
    ];
  }
}
