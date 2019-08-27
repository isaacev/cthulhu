<?php

namespace Cthulhu\IR;

class UseItem extends Item {
  public $symbol;

  function __construct(Symbol3 $symbol) {
    $this->symbol = $symbol;
  }

  public function jsonSerialize() {
    return [
      'type' => 'UseItem',
      'symbol' => $this->symbol
    ];
  }
}