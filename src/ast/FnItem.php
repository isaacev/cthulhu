<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FnItem extends Item {
  public $name;
  public $params;
  public $returns;
  public $body;

  function __construct(Source\Span $span, IdentNode $name, array $polys, array $params, Annotation $returns, BlockNode $body, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->polys = $polys;
    $this->params = $params;
    $this->returns = $returns;
    $this->body = $body;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('FnItem', $visitor_table)) {
      $visitor_table['FnItem']($this);
    }

    $this->name->visit($visitor_table);
    foreach ($this->polys as $poly) {
      $poly->visit($visitor_table);
    }
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
      'polys' => $this->polys,
      'params' => $this->params,
      'returns' => $this->returns,
      'body' => $this->body
    ];
  }
}
