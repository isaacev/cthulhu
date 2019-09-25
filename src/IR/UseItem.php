<?php

namespace Cthulhu\IR;

class UseItem extends Item {
  public $symbol;

  function __construct(Symbol $symbol, array $attrs) {
    parent::__construct($attrs);
    $this->symbol = $symbol;
  }

  public function jsonSerialize() {
    return [
      'type' => 'UseItem',
      'symbol' => $this->symbol
    ];
  }
}
