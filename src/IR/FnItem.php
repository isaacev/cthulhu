<?php

namespace Cthulhu\IR;

class FnItem extends Item {
  public $symbol;
  public $param_symbols;
  public $signature;
  public $scope;
  public $body;

  function __construct(Symbol $symbol, array $param_symbols, Types\FunctionType $signature, BlockScope $scope, BlockNode $body, array $attrs) {
    parent::__construct($attrs);
    $this->symbol        = $symbol;
    $this->param_symbols = $param_symbols;
    $this->signature     = $signature;
    $this->scope         = $scope;
    $this->body          = $body;
  }
}
