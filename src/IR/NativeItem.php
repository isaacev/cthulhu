<?php

namespace Cthulhu\IR;

class NativeItem extends Item {
  public $external;
  public $internal;
  public $type;

  function __construct(Symbol $internal, Symbol $external, Types\FunctionType $type, array $attrs) {
    parent::__construct($attrs);
    $this->internal = $internal;
    $this->external = $external;
    $this->type     = $type;
  }
}
