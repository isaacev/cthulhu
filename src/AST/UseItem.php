<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class UseItem extends Item {
  public $name;

  function __construct(Source\Span $span, IdentNode $name, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('UseItem', $visitor_table)) {
      $visitor_table['UseItem']($this);
    }

    $this->name->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      // TODO
    ];
  }
}
