<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class ModItem extends Item {
  function __construct(Source\Span $span, IdentNode $name, array $items, array $attrs) {
    parent::__construct($span, $attrs);
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
