<?php

namespace Cthulhu\Types;

class Context {
  public $binding;
  public $return_type;

  function __construct(?Binding $binding, Type $return_type) {
    $this->binding = $binding;
    $this->return_type = $return_type;
  }
}
