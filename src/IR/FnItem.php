<?php

namespace Cthulhu\IR;

use Cthulhu\Types\FnType;

class FnItem extends Item {
  public $symbol;
  public $signature;
  public $scope;
  public $body;

  function __construct(Symbol $symbol, FnType $signature, BlockScope $scope, BlockNode $body) {
    $this->symbol = $symbol;
    $this->signature = $signature;
    $this->scope = $scope;
    $this->body = $body;
  }

  public function jsonSerialize() {
    return [
      'type' => 'FnItem',
      'symbol' => $this->symbol,
      'signature' => $this->signature,
      'body' => $this->body
    ];
  }
}
