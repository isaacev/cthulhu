<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR;

class NamespaceScope extends Scope {
  public $parent;

  function __construct(?NamespaceScope $parent) {
    $this->parent = $parent;
  }

  public function has_name(IR\Symbol $symbol): bool {
    return parent::has_symbol_in_table($symbol);
  }

  public function register_name(IR\IdentifierNode $ident): Identifier {
    parent::add_symbol_to_table($ident->symbol, $ident->name);
    return new Identifier($ident->name);
  }

  public function get_name(Symbol $symbol): Identifier {
    return new Identifier(parent::get_name_from_table($symbol));
  }
}
