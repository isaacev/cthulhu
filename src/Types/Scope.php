<?php

namespace Cthulhu\Types;

class Scope {
  private $table;
  private $parent;

  function __construct(?Scope $parent) {
    $this->table = [];
    $this->parent = $parent;
  }
}
