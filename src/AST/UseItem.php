<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class UseItem extends Item {
  public $name;

  function __construct(Span $span, IdentNode $name) {
    parent::__construct($span);
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
