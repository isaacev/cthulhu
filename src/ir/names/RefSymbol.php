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
    $ref = $this->get('text') ?? $this->get_id();
    if ($this->parent === null) {
      return "::$ref";
    } else {
      return "$this->parent::$ref";
    }
  }
}
