<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class ModItem extends Item {
  function __construct(Span $span, IdentNode $name, array $items) {
    parent::__construct($span);
    // TODO
  }

  public function visit(array $visitor_table) {
    if (array_key_exists('ModItem', $visitor_table)) {
      $visitor_table['ModItem']($this);
    }

    // TODO
  }

  public function jsonSerialize() {
    return [
      // TODO
    ];
  }
}