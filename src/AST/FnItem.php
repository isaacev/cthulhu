<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class FnItem extends Item {
  public $name;
  public $params;
  public $returns;
  public $body;

  function __construct(Source\Span $span, IdentNode $name, array $params, Annotation $returns, BlockNode $body) {
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

    $this->name->visit($visitor_table);
    foreach ($this->params as $param) {
      $param->visit($visitor_table);
    }
    $this->returns->visit($visitor_table);
    $this->body->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FnItem',
      'name' => $this->name,
      'params' => $this->params,
      'returns' => $this->returns,
      'body' => $this->body
    ];
  }
}
