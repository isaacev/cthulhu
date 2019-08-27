<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class FnItem extends Item {
  public $name;
  public $params;
  public $returns;
  public $body;

  function __construct(Span $span, IdentNode $name, array $params, Annotation $returns, BlockNode $body) {
    parent::__construct($span);
    $this->name = $name;
    $this->params = $params;
    $this->returns = $returns;
    $this->body = $body;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('FnItem', $visitor_table)) {
      $visitor_table['FnItem']($this);
    }

    // TODO
  }

  public function jsonSerialize() {
    return [
      // TODO
    ];
  }
}