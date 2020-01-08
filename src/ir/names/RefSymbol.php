<?php

namespace Cthulhu\ir\names;

/**
 * @param null|RefSymbol $parent
 */
class RefSymbol extends Symbol {
  public ?RefSymbol $parent;

  public function __construct(?self $parent) {
    parent::__construct();
    $this->parent = $parent;
  }

  public function __toString(): string {
    if ($this->parent === null) {
      return "-> $this->id";
    } else {
      return "$this->parent -> $this->id";
    }
  }
}
